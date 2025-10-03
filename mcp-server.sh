#!/bin/bash

# Wrapper script for Laravel MCP Server
# This ensures clean output by redirecting all potential error sources

cd "$(dirname "$0")"

# Suppress all possible PHP error output
export PHP_CLI_SERVER_WORKERS=1
export XDEBUG_MODE=off

# Run the MCP server with maximum output suppression
exec /opt/homebrew/opt/php@8.2/bin/php \
    -d error_reporting=0 \
    -d log_errors=0 \
    -d display_errors=0 \
    -d display_startup_errors=0 \
    -d html_errors=0 \
    artisan mcp:start test 2>/dev/null

