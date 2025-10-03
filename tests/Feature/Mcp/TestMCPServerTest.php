<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Servers\TestMCPServer;
use App\Mcp\Tools\CalculatorTool;
use App\Mcp\Tools\TextProcessorTool;
use App\Mcp\Tools\WeatherTool;
use App\Mcp\Prompts\CodeGeneratorPrompt;
use App\Mcp\Prompts\TextImproverPrompt;
use App\Mcp\Resources\SystemInfoResource;
use App\Mcp\Resources\DocumentationResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestMCPServerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test calculator tool with addition operation.
     */
    public function test_calculator_tool_addition()
    {
        $response = TestMCPServer::tool(CalculatorTool::class, [
            'operation' => 'add',
            'a' => 10,
            'b' => 5,
        ]);

        $response
            ->assertOk()
            ->assertSee('Result: 10 + 5 = 15');
    }

    /**
     * Test calculator tool with division operation.
     */
    public function test_calculator_tool_division()
    {
        $response = TestMCPServer::tool(CalculatorTool::class, [
            'operation' => 'divide',
            'a' => 20,
            'b' => 4,
        ]);

        $response
            ->assertOk()
            ->assertSee('Result: 20 ÷ 4 = 5');
    }

    /**
     * Test calculator tool division by zero error.
     */
    public function test_calculator_tool_division_by_zero()
    {
        $response = TestMCPServer::tool(CalculatorTool::class, [
            'operation' => 'divide',
            'a' => 10,
            'b' => 0,
        ]);

        $response->assertHasErrors(['Division by zero is not allowed.']);
    }

    /**
     * Test calculator tool validation errors.
     */
    public function test_calculator_tool_validation_errors()
    {
        $response = TestMCPServer::tool(CalculatorTool::class, [
            'operation' => 'invalid',
            'a' => 'not_a_number',
        ]);

        $response->assertHasErrors();
    }

    /**
     * Test text processor tool uppercase operation.
     */
    public function test_text_processor_tool_uppercase()
    {
        $response = TestMCPServer::tool(TextProcessorTool::class, [
            'text' => 'hello world',
            'operation' => 'uppercase',
        ]);

        $response
            ->assertOk()
            ->assertSee('HELLO WORLD');
    }

    /**
     * Test text processor tool word count operation.
     */
    public function test_text_processor_tool_word_count()
    {
        $response = TestMCPServer::tool(TextProcessorTool::class, [
            'text' => 'The quick brown fox',
            'operation' => 'word_count',
        ]);

        $response
            ->assertOk()
            ->assertSee('4 words');
    }

    /**
     * Test weather tool with default units.
     */
    public function test_weather_tool_default_units()
    {
        $response = TestMCPServer::tool(WeatherTool::class, [
            'location' => 'New York City',
        ]);

        $response
            ->assertOk()
            ->assertSee('Weather for New York City')
            ->assertSee('Temperature:')
            ->assertSee('°');
    }

    /**
     * Test weather tool with fahrenheit units.
     */
    public function test_weather_tool_fahrenheit()
    {
        $response = TestMCPServer::tool(WeatherTool::class, [
            'location' => 'Paris',
            'units' => 'fahrenheit',
        ]);

        $response
            ->assertOk()
            ->assertSee('Weather for Paris')
            ->assertSee('° F');
    }

    /**
     * Test code generator prompt.
     */
    public function test_code_generator_prompt()
    {
        $response = TestMCPServer::prompt(CodeGeneratorPrompt::class, [
            'language' => 'PHP',
            'functionality' => 'Create a simple calculator function',
            'style' => 'beginner',
            'include_comments' => true,
        ]);

        $response
            ->assertOk()
            ->assertSee('PHP')
            ->assertSee('calculator');
    }

    /**
     * Test text improver prompt.
     */
    public function test_text_improver_prompt()
    {
        $response = TestMCPServer::prompt(TextImproverPrompt::class, [
            'text' => 'this text needs improvement',
            'improvement_type' => 'professional',
            'explain_changes' => true,
        ]);

        $response
            ->assertOk()
            ->assertSee('professional');
    }

    /**
     * Test system info resource.
     */
    public function test_system_info_resource()
    {
        $response = TestMCPServer::resource(SystemInfoResource::class);

        $response
            ->assertOk()
            ->assertSee('SYSTEM INFORMATION REPORT')
            ->assertSee('Laravel')
            ->assertSee('PHP');
    }

    /**
     * Test documentation resource.
     */
    public function test_documentation_resource()
    {
        $response = TestMCPServer::resource(DocumentationResource::class);

        $response
            ->assertOk()
            ->assertSee('Laravel MCP Test Server API Documentation')
            ->assertSee('Calculator Tool')
            ->assertSee('Text Processor Tool');
    }

    /**
     * Test authenticated tool access.
     */
    public function test_authenticated_tool_access()
    {
        $user = User::factory()->create();

        $response = TestMCPServer::actingAs($user)->tool(CalculatorTool::class, [
            'operation' => 'multiply',
            'a' => 6,
            'b' => 7,
        ]);

        $response
            ->assertOk()
            ->assertSee('Result: 6 × 7 = 42');
    }

    /**
     * Test tool metadata assertions.
     */
    public function test_tool_metadata()
    {
        $response = TestMCPServer::tool(CalculatorTool::class, [
            'operation' => 'add',
            'a' => 1,
            'b' => 1,
        ]);

        $response
            ->assertName('calculator')
            ->assertTitle('Calculator Tool')
            ->assertDescription('Performs basic mathematical operations including addition, subtraction, multiplication, and division.');
    }

    /**
     * Test resource metadata assertions.
     */
    public function test_resource_metadata()
    {
        $response = TestMCPServer::resource(SystemInfoResource::class);

        $response
            ->assertName('system-info')
            ->assertTitle('System Info Resource')
            ->assertDescription('Provides comprehensive system information including PHP version, Laravel version, server details, and application configuration.');
    }

    /**
     * Test prompt metadata assertions.
     */
    public function test_prompt_metadata()
    {
        $response = TestMCPServer::prompt(CodeGeneratorPrompt::class, [
            'language' => 'JavaScript',
            'functionality' => 'Create a hello world function',
        ]);

        $response
            ->assertName('code-generator')
            ->assertTitle('Code Generator Assistant')
            ->assertDescription('Generates code snippets based on specified programming language, functionality, and style preferences.');
    }
}

