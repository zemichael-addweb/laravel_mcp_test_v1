@extends('layouts.app')

@section('title', 'Request Details - CoolBeans Library')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Request Details</h1>
                <p class="mt-1 text-sm text-gray-600">
                    MCP request made on {{ $request->created_at->format('M d, Y H:i:s') }}
                </p>
            </div>
            <div>
                <a href="{{ route('mcp.requests') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    ← Back to History
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Request Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Request Details Card -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Request Information</h3>
                
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium
                                @if($request->status === 'completed') bg-green-100 text-green-800
                                @elseif($request->status === 'failed') bg-red-100 text-red-800
                                @elseif($request->status === 'processing') bg-blue-100 text-blue-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst($request->status) }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Request Type</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($request->request_type) }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Files Found</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $request->files_count }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Processing Time</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $request->formatted_processing_time ?? 'N/A' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Session ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $request->session_id ?? 'N/A' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $request->user_ip ?? 'N/A' }}</dd>
                    </div>
                </dl>

                <div class="mt-6">
                    <dt class="text-sm font-medium text-gray-500">Query Text</dt>
                    <dd class="mt-1 text-sm text-gray-900 bg-gray-50 rounded p-3">
                        {{ $request->request_text }}
                    </dd>
                </div>

                @if($request->search_parameters)
                <div class="mt-6">
                    <dt class="text-sm font-medium text-gray-500">Search Parameters</dt>
                    <dd class="mt-1">
                        <div class="bg-gray-50 rounded p-3">
                            <pre class="text-sm text-gray-900">{{ json_encode($request->search_parameters, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </dd>
                </div>
                @endif

                @if($request->error_message)
                <div class="mt-6">
                    <dt class="text-sm font-medium text-red-500">Error Message</dt>
                    <dd class="mt-1 text-sm text-red-800 bg-red-50 rounded p-3">
                        {{ $request->error_message }}
                    </dd>
                </div>
                @endif
            </div>

            <!-- Response Card -->
            @if($request->response_text)
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Response</h3>
                <div class="bg-gray-50 rounded p-4 max-h-96 overflow-y-auto">
                    <pre class="whitespace-pre-wrap text-sm text-gray-800">{{ $request->response_text }}</pre>
                </div>
            </div>
            @endif

            <!-- Found Files Card -->
            @if($request->found_files && count($request->found_files) > 0)
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Found Files ({{ count($request->found_files) }})</h3>
                <div class="space-y-4">
                    @foreach($request->found_files as $file)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900">
                                    {{ $file['title'] ?? $file['name'] }}
                                </h4>
                                
                                @if(!empty($file['description']))
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ $file['description'] }}
                                </p>
                                @endif
                                
                                <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-500">
                                    @if(!empty($file['type']))
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                        {{ $file['type'] }}
                                    </span>
                                    @endif
                                    
                                    @if(!empty($file['source']))
                                    <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded">
                                        {{ $file['source'] }}
                                    </span>
                                    @endif
                                    
                                    @if(!empty($file['date']))
                                    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded">
                                        {{ $file['date'] }}
                                    </span>
                                    @endif
                                </div>

                                @if(!empty($file['content_sample']))
                                <details class="mt-2">
                                    <summary class="text-xs text-blue-600 hover:text-blue-500 cursor-pointer">
                                        Show content sample
                                    </summary>
                                    <div class="mt-1 text-xs text-gray-600 bg-gray-50 rounded p-2">
                                        {{ $file['content_sample'] }}
                                    </div>
                                </details>
                                @endif
                            </div>
                            
                            @if(!empty($file['url']))
                            <div class="ml-4">
                                <a href="{{ $file['url'] }}" 
                                   target="_blank"
                                   class="text-blue-600 hover:text-blue-500 text-xs font-medium">
                                    View File →
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Stats -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Stats</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Request ID</span>
                        <span class="text-sm font-medium text-gray-900">#{{ $request->id }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Created</span>
                        <span class="text-sm font-medium text-gray-900">{{ $request->created_at->diffForHumans() }}</span>
                    </div>
                    
                    @if($request->updated_at && $request->updated_at != $request->created_at)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Last Updated</span>
                        <span class="text-sm font-medium text-gray-900">{{ $request->updated_at->diffForHumans() }}</span>
                    </div>
                    @endif
                    
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Files Found</span>
                        <span class="text-sm font-medium text-gray-900">{{ $request->files_count }}</span>
                    </div>
                    
                    @if($request->processing_time)
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Processing</span>
                        <span class="text-sm font-medium text-gray-900">{{ $request->formatted_processing_time }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>
                
                <div class="space-y-3">
                    <a href="{{ route('mcp.index') }}?query={{ urlencode($request->request_text) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Run Similar Search
                    </a>
                    
                    @if($request->found_files && count($request->found_files) > 0)
                    <button onclick="exportResults()" 
                            class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Export Results
                    </button>
                    @endif
                </div>
            </div>

            <!-- Browser Info -->
            @if($request->user_agent)
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Browser Info</h3>
                <p class="text-xs text-gray-600 break-words">
                    {{ $request->user_agent }}
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

@if($request->found_files && count($request->found_files) > 0)
<script>
function exportResults() {
    const results = @json($request->found_files);
    const csvContent = "data:text/csv;charset=utf-8," 
        + "Name,Title,Description,Type,Source,Date,URL\n"
        + results.map(file => [
            file.name || '',
            file.title || '',
            (file.description || '').replace(/"/g, '""'),
            file.type || '',
            file.source || '',
            file.date || '',
            file.url || ''
        ].map(field => `"${field}"`).join(",")).join("\n");
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `mcp_search_results_${Date.now()}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endif
@endsection
