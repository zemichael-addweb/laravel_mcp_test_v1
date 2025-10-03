@extends('layouts.app')

@section('title', 'Request History - CoolBeans Library')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">MCP Request History</h1>
        <p class="mt-1 text-sm text-gray-600">
            View all MCP server requests, responses, and performance metrics.
        </p>
    </div>

    @if($requests->count() > 0)
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="divide-y divide-gray-200">
            @foreach($requests as $request)
            <div class="px-6 py-4">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mr-3
                                @if($request->status === 'completed') bg-green-100 text-green-800
                                @elseif($request->status === 'failed') bg-red-100 text-red-800
                                @elseif($request->status === 'processing') bg-blue-100 text-blue-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst($request->status) }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ ucfirst($request->request_type) }}
                            </span>
                        </div>
                        
                        <p class="mt-2 text-sm font-medium text-gray-900">
                            {{ $request->request_text }}
                        </p>
                        
                        <div class="mt-2 text-xs text-gray-500 space-x-4">
                            <span>{{ $request->created_at->format('M d, Y H:i:s') }}</span>
                            @if($request->processing_time)
                            <span>{{ $request->formatted_processing_time }}</span>
                            @endif
                            <span>{{ $request->files_count }} files found</span>
                            @if($request->user_ip)
                            <span>IP: {{ $request->user_ip }}</span>
                            @endif
                        </div>

                        @if($request->error_message)
                        <div class="mt-2 text-xs text-red-600 bg-red-50 rounded p-2">
                            Error: {{ $request->error_message }}
                        </div>
                        @endif

                        @if($request->response_text && $request->status === 'completed')
                        <div class="mt-3">
                            <details class="group">
                                <summary class="text-sm text-blue-600 hover:text-blue-500 cursor-pointer font-medium">
                                    View Response
                                </summary>
                                <div class="mt-2 text-sm text-gray-700 bg-gray-50 rounded p-3 max-h-40 overflow-y-auto">
                                    <pre class="whitespace-pre-wrap">{{ Str::limit($request->response_text, 500) }}</pre>
                                </div>
                            </details>
                        </div>
                        @endif
                    </div>
                    
                    <div class="flex-shrink-0 ml-4">
                        <a href="{{ route('mcp.request.details', $request->id) }}" 
                           class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Pagination -->
    @if($requests->hasPages())
    <div class="mt-6">
        {{ $requests->links() }}
    </div>
    @endif

    @else
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16l2.879-2.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No requests yet</h3>
        <p class="mt-1 text-sm text-gray-500">Start by making a search to see request history here.</p>
        <div class="mt-6">
            <a href="{{ route('mcp.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Make a Search
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
