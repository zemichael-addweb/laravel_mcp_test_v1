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
class KeywordChartDataTool extends Tool
{
    protected string $name = 'keyword_chart_data';
    protected string $description = 'Generate chart-ready data for keyword analysis across the CoolBeans Library. Parse natural language prompts to extract search filters and keywords, then analyze file content to provide counts for visualization. Supports presence vs frequency counting, various matching methods (whole word, partial, case sensitivity), and different search scopes (content, titles, all fields).';

    public function handle(Request $request): Response
    {
        $startTime = microtime(true);
        
        $validated = $request->validate([
            'prompt' => 'required|string|max:2000',
            'count_method' => 'sometimes|in:presence,frequency',
            'match_type' => 'sometimes|in:whole_word,partial,exact_phrase',
            'search_scope' => 'sometimes|in:content_only,titles_only,all_fields',
            'case_sensitive' => 'sometimes|boolean',
            'source_filter' => 'sometimes|nullable|string|max:255',
            'type_filter' => 'sometimes|nullable|string|max:255',
            'chart_type' => 'sometimes|in:bar,line,pie,doughnut',
        ], [
            'prompt.required' => 'You must provide a search prompt.',
            'prompt.string' => 'The prompt must be a string.',
            'prompt.max' => 'The prompt cannot be longer than 2,000 characters.',
            'count_method.in' => 'Count method must be either "presence" or "frequency".',
            'match_type.in' => 'Match type must be one of: whole_word, partial, exact_phrase.',
            'search_scope.in' => 'Search scope must be one of: content_only, titles_only, all_fields.',
            'chart_type.in' => 'Chart type must be one of: bar, line, pie, doughnut.',
        ]);

        $prompt = $validated['prompt'];
        $countMethod = $validated['count_method'] ?? 'presence';
        $matchType = $validated['match_type'] ?? 'whole_word';
        $searchScope = $validated['search_scope'] ?? 'content_only';
        $caseSensitive = $validated['case_sensitive'] ?? false;
        $sourceFilter = $validated['source_filter'] ?? null;
        $typeFilter = $validated['type_filter'] ?? null;
        $chartType = $validated['chart_type'] ?? 'bar';

        $sessionId = session()->getId() ?? uniqid();

        // Parse the prompt to extract search filter and keywords
        $parsedData = $this->parsePrompt($prompt);
        
        if (!$parsedData['keywords']) {
            return Response::text("I couldn't identify any keywords to analyze in your prompt. Please specify keywords in quotes, backticks, or use phrases like 'mentioning X, Y, and Z'.");
        }

        $mcpRequest = McpRequest::create([
            'session_id' => $sessionId,
            'request_text' => $prompt,
            'request_type' => 'keyword_chart_data',
            'search_parameters' => [
                'prompt' => $prompt,
                'keywords' => $parsedData['keywords'],
                'search_filter' => $parsedData['search_filter'],
                'count_method' => $countMethod,
                'match_type' => $matchType,
                'search_scope' => $searchScope,
                'case_sensitive' => $caseSensitive,
                'source_filter' => $sourceFilter,
                'type_filter' => $typeFilter,
            ],
            'status' => McpRequest::STATUS_PROCESSING,
            'user_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        try {
            // Build base query for files
            $baseQuery = File::query()
                ->where('deleted_at', null)
                ->where('index_status', 3);

            // Apply search filter if provided
            if ($parsedData['search_filter']) {
                $this->applySearchFilter($baseQuery, $parsedData['search_filter'], $searchScope, $caseSensitive);
            }

            // Apply additional filters
            if ($sourceFilter) {
                $baseQuery->where('source', 'like', '%' . $sourceFilter . '%');
            }

            if ($typeFilter) {
                $baseQuery->where('type', 'like', '%' . $typeFilter . '%');
            }

            $totalFiles = $baseQuery->count();

            // Analyze each keyword
            $keywordResults = [];
            foreach ($parsedData['keywords'] as $keyword) {
                $result = $this->analyzeKeyword($baseQuery, $keyword, $countMethod, $matchType, $searchScope, $caseSensitive);
                $keywordResults[$keyword] = $result;
            }

            $processingTime = microtime(true) - $startTime;

            // Generate chart-ready response
            $chartData = $this->generateChartData($keywordResults, $parsedData, $totalFiles, $chartType);

            $mcpRequest->update([
                'status' => McpRequest::STATUS_COMPLETED,
                'response_text' => json_encode($chartData),
                'found_files' => null,
                'files_count' => $totalFiles,
                'processing_time' => $processingTime,
            ]);

            return Response::text(json_encode($chartData, JSON_PRETTY_PRINT));

        } catch (\Exception $e) {
            $processingTime = microtime(true) - $startTime;
            
            $mcpRequest->update([
                'status' => McpRequest::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'processing_time' => $processingTime,
            ]);

            return Response::text("Error analyzing keywords: " . $e->getMessage());
        }
    }

    private function parsePrompt($prompt)
    {
        $searchFilter = null;
        $keywords = [];

        // Extract keywords from various formats
        // Format 1: quoted keywords like "evil", "pray"
        preg_match_all('/"([^"]+)"/', $prompt, $quotedMatches);
        if (!empty($quotedMatches[1])) {
            $keywords = array_merge($keywords, $quotedMatches[1]);
        }

        // Format 2: backtick keywords like `god`
        preg_match_all('/`([^`]+)`/', $prompt, $backtickMatches);
        if (!empty($backtickMatches[1])) {
            $keywords = array_merge($keywords, $backtickMatches[1]);
        }

        // Format 3: "mentioning X, Y, and Z" pattern
        if (preg_match('/mentioning\s+(.+?)(?:\s+(?:and|&)\s+(.+?))?(?:\s|$)/i', $prompt, $mentionMatches)) {
            $mentionText = $mentionMatches[1];
            if (isset($mentionMatches[2])) {
                $mentionText .= ', ' . $mentionMatches[2];
            }
            
            // Split by commas and clean up
            $mentionKeywords = array_map('trim', explode(',', str_replace(' and ', ', ', $mentionText)));
            $mentionKeywords = array_map(function($k) { 
                return trim($k, '"\'`'); 
            }, $mentionKeywords);
            
            $keywords = array_merge($keywords, $mentionKeywords);
        }

        // Extract search filter (everything before "and plot" or "plot")
        if (preg_match('/^(.+?)\s+(?:and\s+)?plot\s+a?\s*chart/i', $prompt, $filterMatch)) {
            $filterText = trim($filterMatch[1]);
            // Remove "search" or "find" from the beginning
            $filterText = preg_replace('/^(?:please\s+)?(?:search|find)\s+/i', '', $filterText);
            if ($filterText && !empty($filterText)) {
                $searchFilter = $filterText;
            }
        }

        // Clean and deduplicate keywords
        $keywords = array_unique(array_filter(array_map('trim', $keywords)));

        return [
            'search_filter' => $searchFilter,
            'keywords' => array_values($keywords)
        ];
    }

    private function applySearchFilter($query, $searchFilter, $searchScope, $caseSensitive)
    {
        $operator = $caseSensitive ? 'LIKE BINARY' : 'LIKE';
        $searchTerm = '%' . $searchFilter . '%';

        switch ($searchScope) {
            case 'content_only':
                $query->where(function ($q) use ($operator, $searchTerm) {
                    $q->where('content', $operator, $searchTerm)
                      ->orWhere('content_sample', $operator, $searchTerm);
                });
                break;

            case 'titles_only':
                $query->where(function ($q) use ($operator, $searchTerm) {
                    $q->where('title', $operator, $searchTerm)
                      ->orWhere('name', $operator, $searchTerm);
                });
                break;

            case 'all_fields':
            default:
                $query->where(function ($q) use ($operator, $searchTerm) {
                    $q->where('content', $operator, $searchTerm)
                      ->orWhere('title', $operator, $searchTerm)
                      ->orWhere('name', $operator, $searchTerm)
                      ->orWhere('description', $operator, $searchTerm)
                      ->orWhere('note', $operator, $searchTerm);
                });
                break;
        }
    }

    private function analyzeKeyword($baseQuery, $keyword, $countMethod, $matchType, $searchScope, $caseSensitive)
    {
        // Clone the base query for this keyword
        $keywordQuery = clone $baseQuery;

        // Build the search pattern based on match type
        $searchPattern = $this->buildSearchPattern($keyword, $matchType, $caseSensitive);

        // Apply keyword filter based on search scope
        $this->applyKeywordFilter($keywordQuery, $searchPattern, $searchScope, $caseSensitive, $matchType);

        if ($countMethod === 'presence') {
            // Count files that contain the keyword
            return $keywordQuery->count();
        } else {
            // Count total occurrences of the keyword
            return $this->countKeywordOccurrences($keywordQuery, $keyword, $searchScope, $caseSensitive);
        }
    }

    private function buildSearchPattern($keyword, $matchType, $caseSensitive)
    {
        switch ($matchType) {
            case 'whole_word':
                // Use word boundaries for whole word matching
                return $caseSensitive ? $keyword : strtolower($keyword);
                
            case 'exact_phrase':
                return $keyword;
                
            case 'partial':
            default:
                return '%' . $keyword . '%';
        }
    }

    private function applyKeywordFilter($query, $searchPattern, $searchScope, $caseSensitive, $matchType)
    {
        if ($matchType === 'whole_word') {
            // For whole word matching, we'll use REGEXP
            $regexPattern = '\\b' . preg_quote($searchPattern, '/') . '\\b';
            $regexOperator = $caseSensitive ? 'REGEXP BINARY' : 'REGEXP';
        } else {
            $regexOperator = $caseSensitive ? 'LIKE BINARY' : 'LIKE';
            $regexPattern = $searchPattern;
        }

        switch ($searchScope) {
            case 'content_only':
                $query->where(function ($q) use ($regexOperator, $regexPattern) {
                    $q->where('content', $regexOperator, $regexPattern)
                      ->orWhere('content_sample', $regexOperator, $regexPattern);
                });
                break;

            case 'titles_only':
                $query->where(function ($q) use ($regexOperator, $regexPattern) {
                    $q->where('title', $regexOperator, $regexPattern)
                      ->orWhere('name', $regexOperator, $regexPattern);
                });
                break;

            case 'all_fields':
            default:
                $query->where(function ($q) use ($regexOperator, $regexPattern) {
                    $q->where('content', $regexOperator, $regexPattern)
                      ->orWhere('title', $regexOperator, $regexPattern)
                      ->orWhere('name', $regexOperator, $regexPattern)
                      ->orWhere('description', $regexOperator, $regexPattern)
                      ->orWhere('note', $regexOperator, $regexPattern);
                });
                break;
        }
    }

    private function countKeywordOccurrences($query, $keyword, $searchScope, $caseSensitive)
    {
        $files = $query->get();
        $totalCount = 0;

        $searchFields = $this->getSearchFields($searchScope);
        
        foreach ($files as $file) {
            foreach ($searchFields as $field) {
                if ($file->{$field}) {
                    $content = $caseSensitive ? $file->{$field} : strtolower($file->{$field});
                    $searchKeyword = $caseSensitive ? $keyword : strtolower($keyword);
                    
                    $totalCount += substr_count($content, $searchKeyword);
                }
            }
        }

        return $totalCount;
    }

    private function getSearchFields($searchScope)
    {
        switch ($searchScope) {
            case 'content_only':
                return ['content', 'content_sample'];
            case 'titles_only':
                return ['title', 'name'];
            case 'all_fields':
            default:
                return ['content', 'title', 'name', 'description', 'note'];
        }
    }

    private function generateChartData($keywordResults, $parsedData, $totalFiles, $chartType)
    {
        $labels = array_keys($keywordResults);
        $data = array_values($keywordResults);

        // Suggest colors for the chart
        $colors = [
            'rgba(255, 99, 132, 0.8)',   // Red
            'rgba(54, 162, 235, 0.8)',   // Blue
            'rgba(255, 205, 86, 0.8)',   // Yellow
            'rgba(75, 192, 192, 0.8)',   // Green
            'rgba(153, 102, 255, 0.8)',  // Purple
            'rgba(255, 159, 64, 0.8)',   // Orange
            'rgba(199, 199, 199, 0.8)',  // Grey
            'rgba(83, 102, 255, 0.8)',   // Indigo
        ];

        $borderColors = [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 205, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(199, 199, 199, 1)',
            'rgba(83, 102, 255, 1)',
        ];

        // Prepare chart configuration
        $chartConfig = [
            'type' => $chartType,
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'File Count',
                        'data' => $data,
                        'backgroundColor' => array_slice($colors, 0, count($labels)),
                        'borderColor' => array_slice($borderColors, 0, count($labels)),
                        'borderWidth' => 1
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => $this->generateChartTitle($parsedData, $totalFiles)
                    ],
                    'legend' => [
                        'display' => in_array($chartType, ['pie', 'doughnut'])
                    ]
                ],
                'scales' => $chartType === 'bar' ? [
                    'y' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Number of Files'
                        ]
                    ],
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Keywords'
                        ]
                    ]
                ] : null
            ]
        ];

        return [
            'chart_config' => $chartConfig,
            'raw_data' => [
                'keywords' => $labels,
                'counts' => $data,
                'keyword_results' => $keywordResults
            ],
            'metadata' => [
                'total_files_searched' => $totalFiles,
                'search_filter' => $parsedData['search_filter'],
                'keywords_analyzed' => count($labels),
                'chart_type' => $chartType,
                'max_count' => max($data),
                'min_count' => min($data),
                'average_count' => round(array_sum($data) / count($data), 2)
            ],
            'summary' => $this->generateSummary($keywordResults, $parsedData, $totalFiles)
        ];
    }

    private function generateChartTitle($parsedData, $totalFiles)
    {
        $title = 'Keyword Analysis';
        
        if ($parsedData['search_filter']) {
            $title .= " in files matching '{$parsedData['search_filter']}'";
        }
        
        $title .= " ({$totalFiles} files searched)";
        
        return $title;
    }

    private function generateSummary($keywordResults, $parsedData, $totalFiles)
    {
        $summary = "📊 **Keyword Analysis Results**\n\n";
        
        if ($parsedData['search_filter']) {
            $summary .= "🔍 **Search Filter**: {$parsedData['search_filter']}\n";
        }
        
        $summary .= "📁 **Total Files Searched**: " . number_format($totalFiles) . "\n";
        $summary .= "🔤 **Keywords Analyzed**: " . count($keywordResults) . "\n\n";
        
        $summary .= "**Results by Keyword**:\n";
        
        // Sort by count descending
        arsort($keywordResults);
        
        foreach ($keywordResults as $keyword => $count) {
            $percentage = $totalFiles > 0 ? round(($count / $totalFiles) * 100, 1) : 0;
            $summary .= "• **{$keyword}**: {$count} files ({$percentage}%)\n";
        }
        
        $totalMatches = array_sum($keywordResults);
        $summary .= "\n📈 **Total Matches**: {$totalMatches}\n";
        $summary .= "📊 **Chart Ready**: The data above is formatted for easy visualization.\n";
        
        return $summary;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'prompt' => $schema->string()
                ->description('Natural language prompt describing the chart analysis. Examples: "search coolbeans and plot chart of books mentioning evil, pray, money", "analyze files for keywords `data`, `analysis`, `results`"')
                ->required(),

            'count_method' => $schema->string()
                ->enum(['presence', 'frequency'])
                ->description('Counting method: "presence" counts files containing keyword (default), "frequency" counts total occurrences'),

            'match_type' => $schema->string()
                ->enum(['whole_word', 'partial', 'exact_phrase'])
                ->description('Matching method: "whole_word" (default), "partial" (substring), "exact_phrase"'),

            'search_scope' => $schema->string()
                ->enum(['content_only', 'titles_only', 'all_fields'])
                ->description('Search scope: "content_only" (default), "titles_only", "all_fields"'),

            'case_sensitive' => $schema->boolean()
                ->description('Whether matching should be case-sensitive (default: false)'),

            'source_filter' => $schema->string()
                ->description('Optional: Filter by source before keyword analysis'),

            'type_filter' => $schema->string()
                ->description('Optional: Filter by file type before keyword analysis'),

            'chart_type' => $schema->string()
                ->enum(['bar', 'line', 'pie', 'doughnut'])
                ->description('Suggested chart type for visualization (default: bar)'),
        ];
    }
}
