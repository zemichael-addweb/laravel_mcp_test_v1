<?php

use App\Mcp\Servers\TestMCPServer;
use Laravel\Mcp\Facades\Mcp;

// Register the Test MCP Server for web access
Mcp::web('/mcp/test', TestMCPServer::class)
    ->middleware(['throttle:mcp']);

// Also register as a local server for command line access
Mcp::local('test', TestMCPServer::class);
