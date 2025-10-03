<?php

namespace App\Mcp\Resources;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class ConfigurationGuidelinesResource extends Resource
{
    /**
     * The resource's name.
     */
    protected string $name = 'config-guidelines';

    /**
     * The resource's title.
     */
    protected string $title = 'Configuration Guidelines';

    /**
     * The resource's description.
     */
    protected string $description = 'Best practices and guidelines for configuring and optimizing the Laravel MCP server.';

    /**
     * The resource's URI.
     */
    protected string $uri = 'config://guidelines';

    /**
     * The resource's MIME type.
     */
    protected string $mimeType = 'application/json';

    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $guidelines = $this->generateConfigurationGuidelines();
        
        return Response::text(json_encode($guidelines, JSON_PRETTY_PRINT));
    }

    /**
     * Generate configuration guidelines.
     */
    private function generateConfigurationGuidelines(): array
    {
        return [
            'server_configuration' => [
                'name' => [
                    'description' => 'Choose a descriptive name for your MCP server',
                    'best_practices' => [
                        'Use clear, descriptive names',
                        'Avoid special characters',
                        'Keep it concise but informative'
                    ],
                    'examples' => [
                        'good' => ['Weather API Server', 'Document Processing Server'],
                        'bad' => ['Server123', 'MyServer', '🚀 Cool Server']
                    ]
                ],
                'version' => [
                    'description' => 'Use semantic versioning for your server',
                    'format' => 'MAJOR.MINOR.PATCH',
                    'guidelines' => [
                        'Increment MAJOR for breaking changes',
                        'Increment MINOR for new features',
                        'Increment PATCH for bug fixes'
                    ]
                ],
                'instructions' => [
                    'description' => 'Provide clear instructions for AI models',
                    'best_practices' => [
                        'Be specific about server capabilities',
                        'Mention any limitations or restrictions',
                        'Include context about when to use the server'
                    ]
                ]
            ],
            'tool_configuration' => [
                'naming' => [
                    'convention' => 'Use descriptive, action-oriented names',
                    'examples' => [
                        'calculate_math',
                        'process_text',
                        'get_weather_forecast'
                    ]
                ],
                'descriptions' => [
                    'importance' => 'Critical for AI model understanding',
                    'guidelines' => [
                        'Describe what the tool does',
                        'Mention input requirements',
                        'Include usage examples when helpful'
                    ]
                ],
                'validation' => [
                    'use_laravel_validation' => true,
                    'provide_custom_messages' => true,
                    'validate_all_inputs' => true,
                    'example_rules' => [
                        'required',
                        'string|max:255',
                        'numeric|between:0,100',
                        'in:option1,option2,option3'
                    ]
                ],
                'error_handling' => [
                    'be_specific' => 'Provide clear, actionable error messages',
                    'include_examples' => 'Show users what valid input looks like',
                    'handle_edge_cases' => 'Consider division by zero, empty strings, etc.'
                ]
            ],
            'resource_configuration' => [
                'uri_design' => [
                    'format' => 'protocol://path/to/resource',
                    'examples' => [
                        'docs://api/v1',
                        'config://server/settings',
                        'system://info'
                    ],
                    'guidelines' => [
                        'Use meaningful protocol names',
                        'Structure paths logically',
                        'Keep URIs readable'
                    ]
                ],
                'mime_types' => [
                    'importance' => 'Helps clients understand content format',
                    'common_types' => [
                        'text/plain' => 'Plain text content',
                        'text/markdown' => 'Markdown formatted text',
                        'application/json' => 'JSON data',
                        'text/html' => 'HTML content',
                        'image/png' => 'PNG images'
                    ]
                ],
                'content_guidelines' => [
                    'be_comprehensive' => 'Include all relevant information',
                    'structure_well' => 'Use clear headings and sections',
                    'keep_current' => 'Update content regularly',
                    'make_accessible' => 'Consider different skill levels'
                ]
            ],
            'authentication' => [
                'methods' => [
                    'oauth' => [
                        'description' => 'Most robust option for web servers',
                        'use_cases' => ['Production applications', 'Third-party integrations'],
                        'setup' => 'Requires Laravel Passport'
                    ],
                    'sanctum' => [
                        'description' => 'Simple token-based authentication',
                        'use_cases' => ['Internal applications', 'API tokens'],
                        'setup' => 'Requires Laravel Sanctum'
                    ],
                    'custom' => [
                        'description' => 'Custom middleware for specialized needs',
                        'use_cases' => ['Custom token systems', 'Legacy integrations']
                    ]
                ],
                'best_practices' => [
                    'always_authenticate_production' => true,
                    'use_https_in_production' => true,
                    'implement_rate_limiting' => true,
                    'log_authentication_events' => true
                ]
            ],
            'performance' => [
                'caching' => [
                    'cache_expensive_operations' => true,
                    'use_appropriate_cache_duration' => true,
                    'invalidate_stale_cache' => true
                ],
                'optimization' => [
                    'minimize_database_queries' => true,
                    'use_efficient_algorithms' => true,
                    'handle_large_data_sets_carefully' => true,
                    'implement_pagination_when_needed' => true
                ]
            ],
            'testing' => [
                'unit_tests' => [
                    'test_all_tools' => true,
                    'test_validation_rules' => true,
                    'test_error_conditions' => true,
                    'test_authentication' => true
                ],
                'mcp_inspector' => [
                    'use_for_manual_testing' => true,
                    'test_authentication_flows' => true,
                    'verify_all_capabilities' => true
                ]
            ],
            'deployment' => [
                'environment_configuration' => [
                    'use_environment_variables' => true,
                    'separate_staging_production' => true,
                    'secure_sensitive_data' => true
                ],
                'monitoring' => [
                    'implement_logging' => true,
                    'monitor_performance' => true,
                    'track_usage_metrics' => true,
                    'alert_on_errors' => true
                ]
            ]
        ];
    }
}

