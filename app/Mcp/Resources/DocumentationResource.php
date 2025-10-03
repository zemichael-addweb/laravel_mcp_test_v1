<?php

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class DocumentationResource extends Resource
{
    /**
     * The resource's name.
     */
    protected string $name = 'api-documentation';

    /**
     * The resource's title.
     */
    protected string $title = 'API Documentation';

    /**
     * The resource's description.
     */
    protected string $description = 'Comprehensive API documentation including endpoints, parameters, and examples for the Laravel MCP test server.';

    /**
     * The resource's URI.
     */
    protected string $uri = 'docs://api/v1';

    /**
     * The resource's MIME type.
     */
    protected string $mimeType = 'text/markdown';

    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $documentation = $this->generateApiDocumentation();
        
        return Response::text($documentation);
    }

    /**
     * Generate comprehensive API documentation.
     */
    private function generateApiDocumentation(): string
    {
        return <<<'MARKDOWN'
# Laravel MCP Test Server API Documentation

## Overview
This document provides comprehensive documentation for the Laravel MCP Test Server API endpoints and capabilities.

## Server Information
- **Server Name**: Test MCP Server
- **Version**: 1.0.0
- **Protocol**: Model Context Protocol (MCP)
- **Base URL**: `/mcp/test`

## Authentication
The server supports multiple authentication methods:
- **Bearer Token**: Include `Authorization: Bearer <token>` header
- **OAuth 2.1**: Full OAuth flow with authorization screens
- **Sanctum**: Laravel Sanctum authentication

## Available Tools

### 1. Calculator Tool
Performs basic mathematical operations.

**Parameters:**
- `operation` (string, required): One of `add`, `subtract`, `multiply`, `divide`
- `a` (number, required): First number
- `b` (number, required): Second number

**Example Request:**
```json
{
  "operation": "multiply",
  "a": 7,
  "b": 8
}
```

**Example Response:**
```
Result: 7 × 8 = 56
```

### 2. Text Processor Tool
Processes text with various operations.

**Parameters:**
- `text` (string, required): Text to process (max 10,000 characters)
- `operation` (string, required): One of `uppercase`, `lowercase`, `reverse`, `word_count`, `char_count`, `title_case`

**Example Request:**
```json
{
  "text": "hello world",
  "operation": "title_case"
}
```

**Example Response:**
```
Processed text (title_case):

Hello World
```

### 3. Weather Tool
Provides simulated weather information for demonstration purposes.

**Parameters:**
- `location` (string, required): Location name (max 100 characters)
- `units` (string, optional): Either `celsius` or `fahrenheit` (default: `celsius`)

**Example Request:**
```json
{
  "location": "Paris",
  "units": "celsius"
}
```

**Example Response:**
```
🌤️ Weather for Paris:
Temperature: 22° C
Condition: Partly Cloudy
Humidity: 65%
Wind Speed: 15 km/h
```

## Available Prompts

### 1. Code Generator Prompt
Generates code snippets in various programming languages.

**Arguments:**
- `language` (string, required): Programming language
- `functionality` (string, required): Description of desired functionality
- `style` (string, optional): `beginner`, `intermediate`, `advanced`, or `production`
- `include_comments` (boolean, optional): Include explanatory comments
- `include_tests` (boolean, optional): Include unit tests

### 2. Text Improver Prompt
Improves text quality based on specified criteria.

**Arguments:**
- `text` (string, required): Text to improve (max 5,000 characters)
- `improvement_type` (string, optional): Type of improvement desired
- `target_audience` (string, optional): Intended audience
- `preserve_length` (boolean, optional): Keep similar length
- `explain_changes` (boolean, optional): Explain what was changed

## Available Resources

### 1. System Information Resource
- **URI**: `system://info`
- **MIME Type**: `text/plain`
- **Description**: Comprehensive system and application information

### 2. API Documentation Resource
- **URI**: `docs://api/v1`
- **MIME Type**: `text/markdown`
- **Description**: This documentation resource

### 3. Configuration Guidelines Resource
- **URI**: `config://guidelines`
- **MIME Type**: `application/json`
- **Description**: Server configuration guidelines and best practices

## Error Handling

All tools, prompts, and resources return appropriate error messages when:
- Required parameters are missing
- Invalid parameter values are provided
- Internal server errors occur
- Authentication fails
- Authorization is denied

**Error Response Format:**
```json
{
  "error": "Detailed error message explaining what went wrong and how to fix it."
}
```

## Rate Limiting
Web-based server endpoints are protected with throttling middleware to prevent abuse.

## Testing
Use the MCP Inspector to test server capabilities:
```bash
php artisan mcp:inspector test
```

## Support
For issues or questions, refer to the Laravel MCP documentation or check system logs.

---

Generated at: {current_timestamp}
MARKDOWN;
    }
}

