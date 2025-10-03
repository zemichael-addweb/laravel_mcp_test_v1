@extends('layouts.app')

@section('title', 'CoolBeans Library Search')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Files</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['total_files']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Indexed Files</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['indexed_files']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16l2.879-2.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Searches</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['total_requests']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Successful</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['successful_requests']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Form -->
    <div class="bg-white shadow rounded-lg p-6 mb-8" x-data="searchForm()">
        <div class="mb-6">
            <h2 class="text-lg font-medium text-gray-900">Search CoolBeans Library Database</h2>
            <p class="mt-1 text-sm text-gray-600">
                Search through documents, books, lectures, and other content in the CoolBeans Library. Try queries like "books about prayer from coolbeans" or "lectures on meditation".
            </p>
        </div>

        <form @submit.prevent="submitSearch">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="lg:col-span-2">
                    <label for="query" class="block text-sm font-medium text-gray-700">Search Query</label>
                    <input type="text" 
                           name="query" 
                           id="query"
                           x-model="form.query"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                           placeholder="Enter your search query (e.g., 'books about prayer from coolbeans')"
                           required>
                </div>

                <div>
                    <label for="source" class="block text-sm font-medium text-gray-700">Source (Optional)</label>
                    <input type="text" 
                           name="source" 
                           id="source"
                           x-model="form.source"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                           placeholder="e.g., coolbeans, lectures">
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Type (Optional)</label>
                    <input type="text" 
                           name="type" 
                           id="type"
                           x-model="form.type"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                           placeholder="e.g., book, audio, document">
                </div>

                <div>
                    <label for="response_type" class="block text-sm font-medium text-gray-700">Response Type</label>
                    <select name="response_type" 
                            id="response_type"
                            x-model="form.response_type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="summary">Summary</option>
                        <option value="list">Simple List</option>
                        <option value="detailed">Detailed Information</option>
                    </select>
                </div>

                <div>
                    <label for="limit" class="block text-sm font-medium text-gray-700">Result Limit</label>
                    <select name="limit" 
                            id="limit"
                            x-model="form.limit"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="5">5 results</option>
                        <option value="10">10 results</option>
                        <option value="20">20 results</option>
                        <option value="50">50 results</option>
                    </select>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" 
                        :disabled="loading"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:bg-gray-400 disabled:cursor-not-allowed">
                    <span x-show="!loading">Search Files</span>
                    <span x-show="loading" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Searching...
                    </span>
                </button>
            </div>
        </form>

        <!-- Results -->
        <div x-show="response" class="mt-8">
            <div class="border-t border-gray-200 pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Search Results</h3>
                    <div class="flex items-center space-x-2">
                        <span x-show="searchResult.files_count" class="text-sm text-gray-500">
                            <span x-text="searchResult.files_count"></span> files found
                        </span>
                    </div>
                </div>
                
                <div class="mt-4 bg-gray-50 rounded-md p-4">
                    <pre class="whitespace-pre-wrap text-sm text-gray-800" x-text="response"></pre>
                </div>

                <!-- Action Buttons -->
                <div x-show="searchResult.request_id" class="mt-4 flex flex-wrap gap-3">
                    <a :href="'{{ url('mcp/requests') }}/' + searchResult.request_id" 
                       class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        View Full Details
                    </a>

                    <template x-if="searchResult.response_type !== 'detailed' && searchResult.files_count > 0">
                        <button @click="runDetailedSearch" 
                                :disabled="loading"
                                class="inline-flex items-center px-3 py-2 border border-blue-300 rounded-md shadow-sm text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Get Detailed Results
                        </button>
                    </template>

                    <template x-if="searchResult.response_type !== 'list' && searchResult.files_count > 0">
                        <button @click="runListSearch" 
                                :disabled="loading"
                                class="inline-flex items-center px-3 py-2 border border-green-300 rounded-md shadow-sm text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            Get Simple List
                        </button>
                    </template>

                    <button @click="refreshHistory" 
                            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh History
                    </button>
                </div>
            </div>
        </div>

        <!-- Error -->
        <div x-show="error" class="mt-8">
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-red-600">Error</h3>
                <div class="mt-4 bg-red-50 rounded-md p-4">
                    <p class="text-sm text-red-800" x-text="error"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- MCP Server Integration -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <div class="border-b border-gray-200 pb-4 mb-6">
            <h2 class="text-lg font-medium text-gray-900">🤖 MCP Server Integration</h2>
            <p class="mt-1 text-sm text-gray-600">
                Use the <code>coolbeans_library_search</code> tool with Claude Desktop or other MCP clients
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Claude Desktop Setup -->
            <div>
                <h3 class="text-md font-medium text-gray-900 mb-3">Claude Desktop Configuration</h3>
                <div class="bg-gray-900 rounded-md p-4 text-sm">
                    <pre class="text-green-400"><code>{
  "mcpServers": {
    "coolbeans-library": {
      "command": "php",
      "args": ["artisan", "mcp:start", "test"],
      "cwd": "{{ base_path() }}"
    }
  }
}</code></pre>
                </div>
                <p class="mt-2 text-xs text-gray-500">
                    Add this to: <code>~/Library/Application Support/Claude/claude_desktop_config.json</code>
                </p>
            </div>

            <!-- Example Prompts -->
            <div>
                <h3 class="text-md font-medium text-gray-900 mb-3">Example Natural Language Queries</h3>
                <div class="space-y-3">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <p class="text-sm text-blue-800">
                            "Please get me a summary of books from coolbeans that talk about how to pray"
                        </p>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <p class="text-sm text-blue-800">
                            "Find me lectures about meditation and spiritual development"
                        </p>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <p class="text-sm text-blue-800">
                            "Show me detailed information about documents related to philosophy"
                        </p>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <p class="text-sm text-blue-800">
                            "Give me a list of audio files about personal growth from any source"
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 border-t border-gray-200 pt-4">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex-1 text-sm text-gray-600">
                    <p><strong>How it works:</strong> After configuring Claude Desktop, you can use natural language to search the CoolBeans Library. Claude will automatically use the <code>coolbeans_library_search</code> tool to find relevant documents, books, lectures, and other content based on your queries.</p>
                    <p class="mt-2"><strong>Quick Start:</strong> 1) Add the configuration above to Claude Desktop, 2) Restart Claude Desktop, 3) Start the MCP server with <code class="bg-gray-100 px-1 rounded">php artisan mcp:start test</code>, 4) Ask Claude to search for content!</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Requests -->
    <div x-data="recentRequestsData()" 
         x-show="recentRequests.length > 0" 
         class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">Recent Searches</h2>
            <button @click="refreshRequests" 
                    :disabled="loading"
                    class="text-sm text-blue-600 hover:text-blue-500 disabled:opacity-50">
                <span x-show="!loading">Refresh</span>
                <span x-show="loading">Loading...</span>
            </button>
        </div>
        <div class="divide-y divide-gray-200">
            <template x-for="request in recentRequests" :key="request.id">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate" x-text="request.request_text"></p>
                            <p class="text-xs text-gray-500">
                                <span x-text="request.created_at"></span> • 
                                <span x-text="request.files_count"></span> files found • 
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="{
                                          'bg-green-100 text-green-800': request.status === 'completed',
                                          'bg-red-100 text-red-800': request.status === 'failed',
                                          'bg-yellow-100 text-yellow-800': request.status !== 'completed' && request.status !== 'failed'
                                      }">
                                    <span x-text="request.status.charAt(0).toUpperCase() + request.status.slice(1)"></span>
                                </span>
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <a :href="'{{ url('mcp/requests') }}/' + request.id" 
                               class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function recentRequestsData() {
    return {
        recentRequests: @js($recentRequests->toArray()),
        loading: false,

        async refreshRequests() {
            this.loading = true;
            try {
                const response = await fetch('{{ route("api.recent.requests") }}');
                const data = await response.json();
                if (data.success) {
                    this.recentRequests = data.requests;
                }
            } catch (err) {
                console.error('Failed to refresh requests:', err);
            } finally {
                this.loading = false;
            }
        }
    }
}

function searchForm() {
    return {
        form: {
            query: '',
            source: '',
            type: '',
            response_type: 'summary',
            limit: 10
        },
        response: '',
        error: '',
        loading: false,
        searchResult: {},

        async submitSearch() {
            await this.performSearch();
        },

        async performSearch() {
            this.loading = true;
            this.response = '';
            this.error = '';

            try {
                const response = await fetch('{{ route("mcp.search") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (data.success) {
                    this.response = data.response;
                    this.searchResult = {
                        request_id: data.request_id,
                        files_count: data.files_count,
                        response_type: data.response_type,
                        has_more: data.has_more,
                        query: data.query
                    };
                    // Refresh recent requests to show the new search
                    setTimeout(() => this.refreshRecentRequests(), 500);
                } else {
                    this.error = data.error || 'An error occurred';
                    this.searchResult = {};
                }
            } catch (err) {
                this.error = 'Network error: ' + err.message;
                this.searchResult = {};
            } finally {
                this.loading = false;
            }
        },

        async runDetailedSearch() {
            const originalResponseType = this.form.response_type;
            this.form.response_type = 'detailed';
            await this.performSearch();
            this.form.response_type = originalResponseType;
        },

        async runListSearch() {
            const originalResponseType = this.form.response_type;
            this.form.response_type = 'list';
            await this.performSearch();
            this.form.response_type = originalResponseType;
        },

        async refreshHistory() {
            // Automatically refresh the recent requests after any search
            await this.refreshRecentRequests();
        },

        async refreshRecentRequests() {
            try {
                const response = await fetch('{{ route("api.recent.requests") }}');
                const data = await response.json();
                if (data.success) {
                    // Find the recent requests Alpine component and update it
                    const recentRequestsComponent = document.querySelector('[x-data*="recentRequestsData"]');
                    if (recentRequestsComponent) {
                        const alpineData = Alpine.$data(recentRequestsComponent);
                        if (alpineData && alpineData.recentRequests) {
                            alpineData.recentRequests = data.requests;
                        }
                    }
                }
            } catch (err) {
                console.error('Failed to refresh requests:', err);
            }
        }
    }
}
</script>
@endsection
