<?php

namespace App\Mcp\Tools;

use App\Mcp\Resources\SystemInfoResource;
use App\Models\McpRequest;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[IsReadOnly]
#[IsIdempotent]
class SystemInformationTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Provides comprehensive system information including PHP version, Laravel version, server details, and application configuration.';

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'operation' => 'required|in:php_version,laravel_version,server_details,application_configuration',
        ], [
            'operation.required' => 'You must specify an operation: php_version, laravel_version, server_details, or application_configuration.',
            'operation.in' => 'Operation must be one of: php_version, laravel_version, server_details, or application_configuration.',
        ]);

        $operation = $validated['operation'];

        $startTime = microtime(true);

        // record the request
        $mcpRequest = McpRequest::create([
            'session_id' => session()->getId(),
            'request_text' => "System information request",
            'request_type' => 'system_information',
            'search_parameters' => [],
            'response_text' => '',
            'found_files' => [],
            'files_count' => 0,
            'error_message' => null,
            'status' => McpRequest::STATUS_PROCESSING,
            'user_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);


        // use system information resource
        $systemInfoResource = new SystemInfoResource();
        $systemInfoResponse = $systemInfoResource->handle($request);
        if (in_array($operation, [
            'php_version',
            'laravel_version',
            'server_details',
            'application_configuration'
        ])) {
            $systemInfo = json_decode(json_encode($systemInfoResponse), true);

            $result = match ($operation) {
                'php_version' => $systemInfo['php']['version'] ?? PHP_VERSION,
                'laravel_version' => $systemInfo['laravel']['version'] ?? app()->version(),
                'server_details' => $systemInfo['server'] ?? ($_SERVER['SERVER_SOFTWARE'] ?? 'unknown'),
                'application_configuration' => $systemInfo['application'] ?? config('app.name', 'Laravel'),
            };

            if (is_array($result)) {
                $result = print_r($result, true);
            }

            $processingTime = microtime(true) - $startTime;

            // record the response
            $mcpRequest->update([
                'status' => McpRequest::STATUS_COMPLETED,
                'response_text' => $result,
                'found_files' => [],
                'files_count' => 1,
                'processing_time' => $processingTime,
            ]);
            
            return Response::text((string) $result);
        }
        else {
            return Response::text((string) "Unable to check system information. Please check the application logs.");
        }
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'operation' => $schema->string()
                ->enum(['php_version', 'laravel_version', 'server_details', 'application_configuration'])
                ->description('The system information to check.')
                ->required(),
        ];
    }
}
