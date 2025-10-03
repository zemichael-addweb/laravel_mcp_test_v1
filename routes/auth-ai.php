<?php

use App\Mcp\Servers\TestMCPServer;
use Laravel\Mcp\Facades\Mcp;

/*
|--------------------------------------------------------------------------
| Authenticated MCP Routes
|--------------------------------------------------------------------------
|
| This file demonstrates various authentication methods for MCP servers.
| These are examples showing different authentication approaches you can use.
|
*/

// OAuth 2.1 Authentication with Laravel Passport
// Uncomment these lines when Laravel Passport is installed and configured
/*
Mcp::oauthRoutes();

Mcp::web('/mcp/oauth-server', TestMCPServer::class)
    ->middleware('auth:api');
*/

// Sanctum Authentication
// Uncomment when Laravel Sanctum is configured
/*
Mcp::web('/mcp/sanctum-server', TestMCPServer::class)
    ->middleware('auth:sanctum');
*/

// Custom Authentication Middleware
Mcp::web('/mcp/custom-auth', TestMCPServer::class)
    ->middleware(['throttle:mcp', 'auth.custom.token']);

// Multi-middleware Authentication
Mcp::web('/mcp/secure-server', TestMCPServer::class)
    ->middleware(['auth:sanctum', 'verified', 'throttle:api']);

// Development/Testing Server (no authentication)
if (app()->environment(['local', 'testing'])) {
    Mcp::web('/mcp/dev-server', TestMCPServer::class)
        ->middleware(['throttle:api']);
}

// Premium Server (requires subscription)
Mcp::web('/mcp/premium', TestMCPServer::class)
    ->middleware(['auth:sanctum', 'subscription.active']);

