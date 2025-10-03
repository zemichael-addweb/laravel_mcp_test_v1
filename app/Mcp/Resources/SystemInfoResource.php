<?php

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class SystemInfoResource extends Resource
{
    /**
     * The resource's description.
     */
    protected string $description = 'Provides comprehensive system information including PHP version, Laravel version, server details, and application configuration.';

    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $systemInfo = $this->gatherSystemInfo();
        
        $content = $this->formatSystemInfo($systemInfo);
        
        return Response::text($content);
    }

    /**
     * Get the resource URI.
     */
    public function uri(): string
    {
        return 'system://info';
    }

    /**
     * Get the resource MIME type.
     */
    public function mimeType(): string
    {
        return 'text/plain';
    }

    /**
     * Gather comprehensive system information.
     */
    private function gatherSystemInfo(): array
    {
        $laravel = app();
        
        return [
            'application' => [
                'name' => config('app.name', 'Laravel'),
                'environment' => config('app.env', 'unknown'),
                'debug' => config('app.debug', false),
                'url' => config('app.url', 'unknown'),
                'timezone' => config('app.timezone', 'UTC'),
            ],
            'laravel' => [
                'version' => $laravel->version(),
                'locale' => config('app.locale', 'en'),
                'fallback_locale' => config('app.fallback_locale', 'en'),
            ],
            'php' => [
                'version' => PHP_VERSION,
                'sapi' => php_sapi_name(),
                'extensions_loaded' => count(get_loaded_extensions()),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ],
            'server' => [
                'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
                'os' => PHP_OS,
                'architecture' => php_uname('m'),
                'hostname' => gethostname(),
                'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
                'server_time' => date('Y-m-d H:i:s T'),
            ],
            'database' => [
                'default_connection' => config('database.default', 'unknown'),
                'connections' => array_keys(config('database.connections', [])),
            ],
            'cache' => [
                'default_store' => config('cache.default', 'unknown'),
                'stores' => array_keys(config('cache.stores', [])),
            ],
            'queue' => [
                'default_connection' => config('queue.default', 'unknown'),
                'connections' => array_keys(config('queue.connections', [])),
            ],
            'mail' => [
                'default_mailer' => config('mail.default', 'unknown'),
                'mailers' => array_keys(config('mail.mailers', [])),
            ],
            'broadcasting' => [
                'default_driver' => config('broadcasting.default', 'unknown'),
                'connections' => array_keys(config('broadcasting.connections', [])),
            ],
            'filesystem' => [
                'default_disk' => config('filesystems.default', 'unknown'),
                'disks' => array_keys(config('filesystems.disks', [])),
            ],
        ];
    }

    /**
     * Format system information for display.
     */
    private function formatSystemInfo(array $systemInfo): string
    {
        $output = "🖥️  SYSTEM INFORMATION REPORT\n";
        $output .= str_repeat("=", 50) . "\n\n";

        foreach ($systemInfo as $section => $data) {
            $output .= "📋 " . strtoupper(str_replace('_', ' ', $section)) . "\n";
            $output .= str_repeat("-", 30) . "\n";

            foreach ($data as $key => $value) {
                $label = ucwords(str_replace('_', ' ', $key));
                
                if (is_array($value)) {
                    $output .= sprintf("%-25s: %s\n", $label, implode(', ', $value));
                } elseif (is_bool($value)) {
                    $output .= sprintf("%-25s: %s\n", $label, $value ? 'Yes' : 'No');
                } else {
                    $output .= sprintf("%-25s: %s\n", $label, $value);
                }
            }
            
            $output .= "\n";
        }

        $output .= str_repeat("=", 50) . "\n";
        $output .= "Generated at: " . now()->toDateTimeString() . "\n";

        return $output;
    }
}
