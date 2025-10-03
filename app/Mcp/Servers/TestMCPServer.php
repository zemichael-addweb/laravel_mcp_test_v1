<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CalculatorTool;
use App\Mcp\Tools\CoolbeansLibrarySearchTool;
use App\Mcp\Tools\TextProcessorTool;
use App\Mcp\Tools\WeatherTool;
use App\Mcp\Tools\SystemInformationTool;
use App\Mcp\Prompts\CodeGeneratorPrompt;
use App\Mcp\Prompts\TextImproverPrompt;
use App\Mcp\Resources\SystemInfoResource;
use App\Mcp\Resources\DocumentationResource;
use App\Mcp\Resources\ConfigurationGuidelinesResource;
use App\Mcp\Resources\LogoResource;
use App\Mcp\Resources\PremiumResource;
use Laravel\Mcp\Server;

class TestMCPServer extends Server
{
    /**
     * The MCP server's name.
     */
    protected string $name = 'CoolBeans Library MCP Server';

    /**
     * The MCP server's version.
     */
    protected string $version = '1.0.0';

    /**
     * The MCP server's instructions for the LLM.
     */
    protected string $instructions = 'This is the CoolBeans Library search and analysis server with full Laravel MCP capabilities. It specializes in searching through a comprehensive database of files, books, lectures, and documents to provide summaries, lists, and detailed information. It also includes mathematical calculations, text processing, weather information, code generation, documentation, system information, and premium features for authenticated users.';

    /**
     * The tools registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        CalculatorTool::class,
        CoolbeansLibrarySearchTool::class,
        TextProcessorTool::class,
        WeatherTool::class,
        SystemInformationTool::class,
    ];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        SystemInfoResource::class,
        DocumentationResource::class,
        ConfigurationGuidelinesResource::class,
        LogoResource::class,
        PremiumResource::class,
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        CodeGeneratorPrompt::class,
        TextImproverPrompt::class,
    ];
}
