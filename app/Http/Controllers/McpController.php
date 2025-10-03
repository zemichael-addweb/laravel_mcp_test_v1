<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\McpRequest;
use App\Mcp\Tools\CoolbeansLibrarySearchTool;
use Illuminate\Http\Request;
use Laravel\Mcp\Request as McpRequestObj;

class McpController extends Controller
{
    public function index()
    {
        $recentRequests = McpRequest::with([])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $stats = [
            'total_files' => File::where('deleted_at', null)->count(),
            'indexed_files' => File::where('deleted_at', null)->where('index_status', 3)->count(),
            'total_requests' => McpRequest::count(),
            'successful_requests' => McpRequest::where('status', McpRequest::STATUS_COMPLETED)->count(),
        ];

        return view('mcp.index', compact('recentRequests', 'stats'));
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|max:1000',
            'source' => 'sometimes|nullable|string|max:255',
            'type' => 'sometimes|nullable|string|max:255',
            'response_type' => 'sometimes|in:summary,list,detailed',
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);

        try {
            // Direct search implementation for web interface
            $startTime = microtime(true);
            
            $query = $validated['query'];
            $source = $validated['source'] ?? null;
            $type = $validated['type'] ?? null;
            $responseType = $validated['response_type'] ?? 'summary';
            $limit = $validated['limit'] ?? 10;

            $sessionId = session()->getId() ?? uniqid();

            // Create MCP request record
            $mcpRequest = McpRequest::create([
                'session_id' => $sessionId,
                'request_text' => $query,
                'request_type' => $responseType,
                'search_parameters' => [
                    'query' => $query,
                    'source' => $source,
                    'type' => $type,
                    'limit' => $limit,
                ],
                'status' => McpRequest::STATUS_PROCESSING,
                'user_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Perform the search
            $filesQuery = File::query()
                ->where('deleted_at', null)
                ->where('index_status', 3);

            $filesQuery->searchContent($query);

            if ($source) {
                $filesQuery->bySource($source);
            }

            if ($type) {
                $filesQuery->byType($type);
            }

            $files = $filesQuery
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            $processingTime = microtime(true) - $startTime;

            // Prepare found files data
            $foundFiles = $files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->name,
                    'title' => $file->title,
                    'description' => $file->description,
                    'type' => $file->type,
                    'source' => $file->source,
                    'date' => $file->date?->format('Y-m-d'),
                    'url' => $file->url,
                    'content_sample' => $file->content_sample ? substr($file->content_sample, 0, 200) : null,
                ];
            })->toArray();

            // Generate response text
            $responseText = $this->generateResponseText($files, $responseType, $query);

            // Update MCP request with results
            $mcpRequest->update([
                'status' => McpRequest::STATUS_COMPLETED,
                'response_text' => $responseText,
                'found_files' => $foundFiles,
                'files_count' => $files->count(),
                'processing_time' => $processingTime,
            ]);
            
            return response()->json([
                'success' => true,
                'response' => $responseText,
                'query' => $validated['query'],
                'request_id' => $mcpRequest->id,
                'files_count' => $files->count(),
                'response_type' => $responseType,
                'has_more' => $files->count() >= $limit
            ]);

        } catch (\Exception $e) {
            // Update MCP request with error
            if (isset($mcpRequest)) {
                $mcpRequest->update([
                    'status' => McpRequest::STATUS_FAILED,
                    'error_message' => $e->getMessage(),
                    'processing_time' => microtime(true) - $startTime,
                ]);
            }
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function generateResponseText($files, $responseType, $query)
    {
        if ($files->isEmpty()) {
            return "I couldn't find any files matching your query: '{$query}'. You might try:\n- Using different keywords\n- Checking the spelling\n- Using broader search terms\n- Specifying a different source or type";
        }

        $count = $files->count();
        $response = "Found {$count} file(s) matching '{$query}':\n\n";

        switch ($responseType) {
            case 'list':
                return $this->generateList($files, $response);
            
            case 'detailed':
                return $this->generateDetailed($files, $response);
            
            case 'summary':
            default:
                return $this->generateSummary($files, $response, $query);
        }
    }

    private function generateList($files, $response)
    {
        foreach ($files as $index => $file) {
            $response .= ($index + 1) . ". ";
            $response .= $file->title ?: $file->name;
            
            if ($file->date) {
                $response .= " (" . $file->date->format('Y-m-d') . ")";
            }
            
            if ($file->source) {
                $response .= " - Source: " . $file->source;
            }
            
            $response .= "\n";
        }

        return $response;
    }

    private function generateDetailed($files, $response)
    {
        foreach ($files as $index => $file) {
            $response .= "--- File " . ($index + 1) . " ---\n";
            $response .= "Title: " . ($file->title ?: $file->name) . "\n";
            
            if ($file->description) {
                $response .= "Description: " . $file->description . "\n";
            }
            
            if ($file->type) {
                $response .= "Type: " . $file->type . "\n";
            }
            
            if ($file->source) {
                $response .= "Source: " . $file->source . "\n";
            }
            
            if ($file->date) {
                $response .= "Date: " . $file->date->format('Y-m-d') . "\n";
            }
            
            if ($file->content_sample) {
                $response .= "Content Sample: " . substr($file->content_sample, 0, 300) . "...\n";
            }
            
            $response .= "\n";
        }

        return $response;
    }

    private function generateSummary($files, $response, $query)
    {
        $types = $files->pluck('type')->filter()->unique();
        $sources = $files->pluck('source')->filter()->unique();
        
        if ($types->isNotEmpty()) {
            $response .= "Types found: " . $types->implode(', ') . "\n";
        }
        
        if ($sources->isNotEmpty()) {
            $response .= "Sources: " . $sources->implode(', ') . "\n\n";
        }

        $response .= "Top results:\n";
        
        foreach ($files->take(5) as $index => $file) {
            $response .= ($index + 1) . ". " . ($file->title ?: $file->name);
            
            if ($file->date) {
                $response .= " (" . $file->date->format('Y-m-d') . ")";
            }
            
            if ($file->description) {
                $response .= " - " . substr($file->description, 0, 100);
                if (strlen($file->description) > 100) {
                    $response .= "...";
                }
            }
            
            $response .= "\n";
        }

        if ($files->count() > 5) {
            $remaining = $files->count() - 5;
            $response .= "\n... and {$remaining} more results. Use response_type='detailed' for more information.";
        }

        return $response;
    }

    public function requests()
    {
        $requests = McpRequest::orderBy('created_at', 'desc')
            ->paginate(20);

        return view('mcp.requests', compact('requests'));
    }

    public function requestDetails($id)
    {
        $request = McpRequest::findOrFail($id);
        
        return view('mcp.request-details', compact('request'));
    }

    public function apiSearch(Request $request)
    {
        return $this->search($request);
    }

    public function recentRequests()
    {
        $recentRequests = McpRequest::with([])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'requests' => $recentRequests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'request_text' => $request->request_text,
                    'status' => $request->status,
                    'files_count' => $request->files_count,
                    'created_at' => $request->created_at->diffForHumans(),
                    'processing_time' => $request->formatted_processing_time,
                ];
            })
        ]);
    }
}
