<?php

namespace App\Mcp\Tools;

use App\Models\File;
use App\Models\McpRequest;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class CoolbeansLibrarySearchTool extends Tool
{
    protected string $name = 'coolbeans_library_search';
    protected string $description = 'Comprehensive search tool for the CoolBeans Library database. Features: 1) Smart search across multiple fields (titles, content, descriptions, notes) with intelligent mode detection, 2) Content summaries - get full text content of specific files by name or ID, 3) Multiple response formats (summary with IDs, detailed lists, content summaries), 4) Advanced filtering by source/type, 5) Handles natural language queries like "books about prayer", "summary of [filename]", "exclude content searches".';

    public function handle(Request $request): Response
    {
        $startTime = microtime(true);
        
        $validated = $request->validate([
            'query' => 'required|string|max:1000',
            'source' => 'sometimes|nullable|string|max:255',
            'type' => 'sometimes|nullable|string|max:255',
            'response_type' => 'sometimes|in:summary,list,detailed,content_summary',
            'limit' => 'sometimes|integer|min:1|max:50',
            'search_mode' => 'sometimes|in:comprehensive,name_only,content_only,exclude_content,partial_match',
            'file_id' => 'sometimes|integer',
        ], [
            'query.required' => 'You must provide a search query.',
            'query.string' => 'The query must be a string.',
            'query.max' => 'The query cannot be longer than 1,000 characters.',
            'response_type.in' => 'Response type must be one of: summary, list, detailed, or content_summary.',
            'limit.integer' => 'Limit must be an integer.',
            'limit.min' => 'Limit must be at least 1.',
            'limit.max' => 'Limit cannot exceed 50.',
            'search_mode.in' => 'Search mode must be one of: comprehensive, name_only, content_only, exclude_content, or partial_match.',
            'file_id.integer' => 'File ID must be an integer.',
        ]);

        $query = $validated['query'];
        $source = $validated['source'] ?? null;
        $type = $validated['type'] ?? null;
        $responseType = $validated['response_type'] ?? 'summary';
        $limit = $validated['limit'] ?? 10;
        $searchMode = $validated['search_mode'] ?? $this->determineSearchMode($query);
        $fileId = $validated['file_id'] ?? null;

        $sessionId = session()->getId() ?? uniqid();

        // Handle content summary request for specific file
        if ($responseType === 'content_summary' && $fileId) {
            return $this->handleContentSummary($fileId, $query, $sessionId, $startTime);
        }

        // Handle content summary by name/title
        if ($responseType === 'content_summary' && !$fileId) {
            return $this->handleContentSummaryByName($query, $sessionId, $startTime);
        }

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
            'user_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        try {
            $filesQuery = File::query()
                ->where('deleted_at', null)
                ->where('index_status', 3);

            // Apply search based on search mode
            $this->applySearchMode($filesQuery, $query, $searchMode);

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
                    'note' => $file->note,
                    'file_number' => $file->file_number,
                    'lecture_code' => $file->lecture_code,
                    'has_content' => !empty($file->content),
                    'content_length' => $file->content ? strlen($file->content) : 0,
                ];
            })->toArray();

            $response = $this->generateResponse($files, $responseType, $query);

            $mcpRequest->update([
                'status' => McpRequest::STATUS_COMPLETED,
                'response_text' => $response,
                'found_files' => $foundFiles,
                'files_count' => $files->count(),
                'processing_time' => $processingTime,
            ]);

            return Response::text($response);

        } catch (\Exception $e) {
            $processingTime = microtime(true) - $startTime;
            
            $mcpRequest->update([
                'status' => McpRequest::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'processing_time' => $processingTime,
            ]);

            return Response::text("I encountered an error while searching: " . $e->getMessage());
        }
    }

    private function generateResponse($files, $responseType, $query)
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
            $response .= "[ID: {$file->id}] " . ($file->title ?: $file->name);
            
            if ($file->date) {
                $response .= " (" . $file->date->format('Y-m-d') . ")";
            }
            
            if ($file->source) {
                $response .= " - Source: " . $file->source;
            }
            
            if ($file->description) {
                $response .= "\n   📝 " . substr($file->description, 0, 150);
                if (strlen($file->description) > 150) {
                    $response .= "...";
                }
            }
            
            $response .= "\n";
        }

        $response .= "\n💡 **Tip**: For content summary of any file, ask: \"Please give me a brief summary of [filename]\" or use the file ID.";

        return $response;
    }

    private function generateDetailed($files, $response)
    {
        foreach ($files as $index => $file) {
            $response .= "--- File " . ($index + 1) . " ---\n";
            $response .= "🆔 **ID**: " . $file->id . "\n";
            $response .= "📄 **Title**: " . ($file->title ?: $file->name) . "\n";
            
            if ($file->description) {
                $response .= "📝 **Description**: " . $file->description . "\n";
            }
            
            if ($file->type) {
                $response .= "📂 **Type**: " . $file->type . "\n";
            }
            
            if ($file->source) {
                $response .= "📖 **Source**: " . $file->source . "\n";
            }
            
            if ($file->date) {
                $response .= "📅 **Date**: " . $file->date->format('Y-m-d') . "\n";
            }

            if ($file->file_number) {
                $response .= "🔢 **File Number**: " . $file->file_number . "\n";
            }

            if ($file->lecture_code) {
                $response .= "📚 **Lecture Code**: " . $file->lecture_code . "\n";
            }

            if (!empty($file->content)) {
                $contentLength = strlen($file->content);
                $response .= "📊 **Content Available**: " . number_format($contentLength) . " characters\n";
            }
            
            if ($file->content_sample) {
                $response .= "📖 **Content Sample**: " . substr($file->content_sample, 0, 300);
                if (strlen($file->content_sample) > 300) {
                    $response .= "...";
                }
                $response .= "\n";
            }

            if ($file->note) {
                $response .= "📋 **Note**: " . substr($file->note, 0, 200);
                if (strlen($file->note) > 200) {
                    $response .= "...";
                }
                $response .= "\n";
            }
            
            $response .= "💡 **Get Full Content**: Ask \"Please give me a brief summary of ID {$file->id}\" or \"Please give me a brief summary of '{$file->name}'\"\n";
            $response .= "\n";
        }

        return $response;
    }

    private function generateSummary($files, $response, $query)
    {
        $types = $files->pluck('type')->filter()->unique();
        $sources = $files->pluck('source')->filter()->unique();
        $withContent = $files->filter(function($file) { return !empty($file->content); })->count();
        
        if ($types->isNotEmpty()) {
            $response .= "📂 **Types found**: " . $types->implode(', ') . "\n";
        }
        
        if ($sources->isNotEmpty()) {
            $response .= "📖 **Sources**: " . $sources->implode(', ') . "\n";
        }

        $response .= "📊 **Content Available**: {$withContent} out of {$files->count()} files have full content\n\n";

        $response .= "**Top results**:\n";
        
        foreach ($files->take(5) as $index => $file) {
            $response .= ($index + 1) . ". **[ID: {$file->id}]** " . ($file->title ?: $file->name);
            
            if ($file->date) {
                $response .= " (" . $file->date->format('Y-m-d') . ")";
            }
            
            if ($file->source) {
                $response .= " - *{$file->source}*";
            }
            
            if ($file->description) {
                $response .= "\n   📝 " . substr($file->description, 0, 120);
                if (strlen($file->description) > 120) {
                    $response .= "...";
                }
            }

            if (!empty($file->content)) {
                $response .= "\n   📊 Content: " . number_format(strlen($file->content)) . " characters";
            }
            
            $response .= "\n";
        }

        if ($files->count() > 5) {
            $remaining = $files->count() - 5;
            $response .= "\n... and **{$remaining} more results**. Use `response_type='detailed'` for complete information.\n";
        }

        $response .= "\n💡 **Get content summary**: Ask \"Please give me a brief summary of [filename]\" or \"Please give me a brief summary of ID [number]\"";

        return $response;
    }

    private function determineSearchMode($query)
    {
        $queryLower = strtolower($query);
        
        // Check for explicit mode indicators
        if (str_contains($queryLower, 'dont look through content') || 
            str_contains($queryLower, "don't look through content") ||
            str_contains($queryLower, 'exclude content') ||
            str_contains($queryLower, 'without content')) {
            return 'exclude_content';
        }
        
        if (str_contains($queryLower, 'partial match') || 
            str_contains($queryLower, 'similar to') ||
            str_contains($queryLower, 'like')) {
            return 'partial_match';
        }
        
        if (str_contains($queryLower, 'just find a file named') || 
            str_contains($queryLower, 'file named') ||
            str_contains($queryLower, 'contains word') ||
            str_contains($queryLower, 'file called')) {
            return 'name_only';
        }
        
        if (str_contains($queryLower, 'content only') || 
            str_contains($queryLower, 'search content') ||
            str_contains($queryLower, 'inside content')) {
            return 'content_only';
        }
        
        return 'comprehensive';
    }

    private function applySearchMode($queryBuilder, $searchTerm, $searchMode)
    {
        switch ($searchMode) {
            case 'name_only':
                $queryBuilder->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('title', 'like', '%' . $searchTerm . '%')
                      ->orWhere('file_label', 'like', '%' . $searchTerm . '%');
                });
                break;
                
            case 'content_only':
                $queryBuilder->where(function ($q) use ($searchTerm) {
                    $q->where('content', 'like', '%' . $searchTerm . '%')
                      ->orWhere('content_sample', 'like', '%' . $searchTerm . '%');
                });
                break;
                
            case 'exclude_content':
                $queryBuilder->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('title', 'like', '%' . $searchTerm . '%')
                      ->orWhere('description', 'like', '%' . $searchTerm . '%')
                      ->orWhere('note', 'like', '%' . $searchTerm . '%')
                      ->orWhere('file_label', 'like', '%' . $searchTerm . '%')
                      ->orWhere('alternate_title_per_the_transcript', 'like', '%' . $searchTerm . '%');
                });
                break;
                
            case 'partial_match':
                $words = explode(' ', $searchTerm);
                $queryBuilder->where(function ($q) use ($words) {
                    foreach ($words as $word) {
                        if (strlen(trim($word)) > 2) {
                            $q->orWhere('content', 'like', '%' . trim($word) . '%')
                              ->orWhere('title', 'like', '%' . trim($word) . '%')
                              ->orWhere('name', 'like', '%' . trim($word) . '%')
                              ->orWhere('description', 'like', '%' . trim($word) . '%');
                        }
                    }
                });
                break;
                
            case 'comprehensive':
            default:
                $queryBuilder->where(function ($q) use ($searchTerm) {
                    $q->where('content', 'like', '%' . $searchTerm . '%')
                      ->orWhere('title', 'like', '%' . $searchTerm . '%')
                      ->orWhere('description', 'like', '%' . $searchTerm . '%')
                      ->orWhere('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('content_sample', 'like', '%' . $searchTerm . '%')
                      ->orWhere('note', 'like', '%' . $searchTerm . '%')
                      ->orWhere('file_label', 'like', '%' . $searchTerm . '%')
                      ->orWhere('alternate_title_per_the_transcript', 'like', '%' . $searchTerm . '%')
                      ->orWhere('lecture_series_original_names', 'like', '%' . $searchTerm . '%')
                      ->orWhere('mimeo_titles_not_in_tech_vols', 'like', '%' . $searchTerm . '%')
                      ->orWhere('lrh_article', 'like', '%' . $searchTerm . '%');
                });
                break;
        }
    }

    private function handleContentSummary($fileId, $query, $sessionId, $startTime)
    {
        $mcpRequest = McpRequest::create([
            'session_id' => $sessionId,
            'request_text' => "Content summary for file ID: {$fileId} - {$query}",
            'request_type' => 'content_summary',
            'search_parameters' => [
                'file_id' => $fileId,
                'query' => $query,
            ],
            'status' => McpRequest::STATUS_PROCESSING,
            'user_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        try {
            $file = File::where('id', $fileId)
                ->where('deleted_at', null)
                ->first();

            if (!$file) {
                $mcpRequest->update([
                    'status' => McpRequest::STATUS_FAILED,
                    'error_message' => 'File not found',
                    'processing_time' => microtime(true) - $startTime,
                ]);
                
                return Response::text("File with ID {$fileId} not found.");
            }

            $summary = $this->generateContentSummary($file, $query);
            $processingTime = microtime(true) - $startTime;

            $mcpRequest->update([
                'status' => McpRequest::STATUS_COMPLETED,
                'response_text' => $summary,
                'found_files' => [['id' => $file->id, 'name' => $file->name, 'title' => $file->title]],
                'files_count' => 1,
                'processing_time' => $processingTime,
            ]);

            return Response::text($summary);

        } catch (\Exception $e) {
            $mcpRequest->update([
                'status' => McpRequest::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'processing_time' => microtime(true) - $startTime,
            ]);

            return Response::text("Error retrieving content summary: " . $e->getMessage());
        }
    }

    private function handleContentSummaryByName($query, $sessionId, $startTime)
    {
        $mcpRequest = McpRequest::create([
            'session_id' => $sessionId,
            'request_text' => "Content summary by name: {$query}",
            'request_type' => 'content_summary',
            'search_parameters' => ['query' => $query],
            'status' => McpRequest::STATUS_PROCESSING,
            'user_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        try {
            // Extract file name from query
            $fileName = $this->extractFileNameFromQuery($query);
            
            $file = File::where('deleted_at', null)
                ->where(function ($q) use ($fileName) {
                    $q->where('name', 'like', '%' . $fileName . '%')
                      ->orWhere('title', 'like', '%' . $fileName . '%');
                })
                ->first();

            if (!$file) {
                $mcpRequest->update([
                    'status' => McpRequest::STATUS_FAILED,
                    'error_message' => 'File not found by name',
                    'processing_time' => microtime(true) - $startTime,
                ]);
                
                return Response::text("File '{$fileName}' not found. Please check the name and try again.");
            }

            $summary = $this->generateContentSummary($file, $query);
            $processingTime = microtime(true) - $startTime;

            $mcpRequest->update([
                'status' => McpRequest::STATUS_COMPLETED,
                'response_text' => $summary,
                'found_files' => [['id' => $file->id, 'name' => $file->name, 'title' => $file->title]],
                'files_count' => 1,
                'processing_time' => $processingTime,
            ]);

            return Response::text($summary);

        } catch (\Exception $e) {
            $mcpRequest->update([
                'status' => McpRequest::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'processing_time' => microtime(true) - $startTime,
            ]);

            return Response::text("Error retrieving content summary: " . $e->getMessage());
        }
    }

    private function extractFileNameFromQuery($query)
    {
        // Extract filename from quotes
        if (preg_match('/"([^"]+)"/', $query, $matches)) {
            return $matches[1];
        }
        
        // Extract filename after "of the book" or similar phrases
        $patterns = [
            '/(?:of the book|of|for|about)\s+(.+?)(?:\s|$)/i',
            '/(?:summery|summary)\s+(?:of|for)\s+(.+)/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $query, $matches)) {
                return trim($matches[1]);
            }
        }
        
        // Fallback: use the whole query minus common words
        $commonWords = ['please', 'give', 'me', 'brief', 'summary', 'summery', 'of', 'the', 'book', 'file', 'document'];
        $words = explode(' ', strtolower($query));
        $cleanWords = array_diff($words, $commonWords);
        
        return implode(' ', array_slice($cleanWords, 0, 3));
    }

    private function generateContentSummary($file, $query = '')
    {
        $response = "=== CONTENT SUMMARY ===\n\n";
        $response .= "📄 **File**: " . ($file->title ?: $file->name) . "\n";
        $response .= "🆔 **ID**: " . $file->id . "\n";
        
        if ($file->source) {
            $response .= "📖 **Source**: " . $file->source . "\n";
        }
        
        if ($file->type) {
            $response .= "📂 **Type**: " . $file->type . "\n";
        }
        
        if ($file->date) {
            $response .= "📅 **Date**: " . $file->date->format('Y-m-d') . "\n";
        }

        if ($file->description) {
            $response .= "📝 **Description**: " . $file->description . "\n";
        }

        $response .= "\n--- CONTENT ---\n";

        if (empty($file->content)) {
            $response .= "⚠️  No content available for this file.\n";
            
            if ($file->content_sample) {
                $response .= "\n**Available Content Sample**:\n" . substr($file->content_sample, 0, 500) . "...\n";
            }
            
            return $response;
        }

        $contentLength = strlen($file->content);
        $response .= "📊 **Content Length**: " . number_format($contentLength) . " characters\n\n";

        // Provide manageable portion of content
        if ($contentLength <= 2000) {
            $response .= "**Full Content**:\n" . $file->content;
        } elseif ($contentLength <= 5000) {
            $response .= "**Content** (showing first 1,500 characters):\n";
            $response .= substr($file->content, 0, 1500) . "\n\n[Content continues... " . number_format($contentLength - 1500) . " characters remaining]";
        } else {
            // For very long content, provide beginning and relevant excerpts
            $response .= "**Content Summary** (showing key excerpts):\n\n";
            $response .= "**Beginning**:\n" . substr($file->content, 0, 800) . "\n\n";
            
            // Try to find relevant sections if query provided
            if ($query && strlen($query) > 3) {
                $queryWords = explode(' ', strtolower($query));
                foreach ($queryWords as $word) {
                    if (strlen($word) > 3) {
                        $pos = stripos($file->content, $word);
                        if ($pos !== false) {
                            $start = max(0, $pos - 200);
                            $excerpt = substr($file->content, $start, 400);
                            $response .= "**Relevant Section** (contains '{$word}'):\n..." . $excerpt . "...\n\n";
                            break;
                        }
                    }
                }
            }
            
            $response .= "**End**:\n..." . substr($file->content, -400) . "\n\n";
            $response .= "[Total content length: " . number_format($contentLength) . " characters]";
        }

        return $response;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('Search query or content summary request. For searches: keywords, topics, titles. For summaries: "brief summary of [filename]" or natural language request.')
                ->required(),

            'source' => $schema->string()
                ->description('Optional: Filter by source (e.g., "coolbeans", "lectures", "archive")'),

            'type' => $schema->string()
                ->description('Optional: Filter by file type (e.g., "book", "audio", "document", "pdf")'),

            'response_type' => $schema->string()
                ->enum(['summary', 'list', 'detailed', 'content_summary'])
                ->description('Response format: summary (overview + top results), list (simple list with IDs), detailed (full metadata), content_summary (full content of specific file)'),

            'search_mode' => $schema->string()
                ->enum(['comprehensive', 'name_only', 'content_only', 'exclude_content', 'partial_match'])
                ->description('Search strategy: comprehensive (all fields), name_only (titles/names only), content_only (document content), exclude_content (no content search), partial_match (individual words)'),

            'file_id' => $schema->integer()
                ->description('Optional: Specific file ID for content summary requests'),

            'limit' => $schema->integer()
                ->description('Maximum search results (1-50, default: 10)'),
        ];
    }
}
