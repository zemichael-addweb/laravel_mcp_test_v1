<?php

namespace Tests\Unit\Mcp;

use App\Mcp\Tools\CalculatorTool;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use PHPUnit\Framework\TestCase;

class CalculatorToolTest extends TestCase
{
    private CalculatorTool $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tool = new CalculatorTool();
    }

    /**
     * Test tool has correct name and title.
     */
    public function test_tool_metadata()
    {
        $reflection = new \ReflectionClass($this->tool);
        
        // Test that the tool has the expected properties
        $this->assertTrue($reflection->hasProperty('description'));
        
        $description = $reflection->getProperty('description');
        $description->setAccessible(true);
        
        $this->assertStringContainsString('mathematical operations', $description->getValue($this->tool));
    }

    /**
     * Test tool schema returns correct structure.
     */
    public function test_tool_schema()
    {
        $schema = new JsonSchema();
        $result = $this->tool->schema($schema);

        $this->assertArrayHasKey('operation', $result);
        $this->assertArrayHasKey('a', $result);
        $this->assertArrayHasKey('b', $result);
        
        // Test operation field properties
        $operationSchema = $result['operation'];
        $this->assertInstanceOf(JsonSchema::class, $operationSchema);
    }

    /**
     * Test addition operation.
     */
    public function test_addition()
    {
        $request = $this->createMockRequest([
            'operation' => 'add',
            'a' => 5,
            'b' => 3
        ]);

        $response = $this->tool->handle($request);
        
        $this->assertEquals('Result: 5 + 3 = 8', $response->getText());
    }

    /**
     * Test subtraction operation.
     */
    public function test_subtraction()
    {
        $request = $this->createMockRequest([
            'operation' => 'subtract',
            'a' => 10,
            'b' => 4
        ]);

        $response = $this->tool->handle($request);
        
        $this->assertEquals('Result: 10 - 4 = 6', $response->getText());
    }

    /**
     * Test multiplication operation.
     */
    public function test_multiplication()
    {
        $request = $this->createMockRequest([
            'operation' => 'multiply',
            'a' => 7,
            'b' => 6
        ]);

        $response = $this->tool->handle($request);
        
        $this->assertEquals('Result: 7 × 6 = 42', $response->getText());
    }

    /**
     * Test division operation.
     */
    public function test_division()
    {
        $request = $this->createMockRequest([
            'operation' => 'divide',
            'a' => 15,
            'b' => 3
        ]);

        $response = $this->tool->handle($request);
        
        $this->assertEquals('Result: 15 ÷ 3 = 5', $response->getText());
    }

    /**
     * Test division by zero returns error.
     */
    public function test_division_by_zero()
    {
        $request = $this->createMockRequest([
            'operation' => 'divide',
            'a' => 10,
            'b' => 0
        ]);

        $response = $this->tool->handle($request);
        
        $this->assertTrue($response->isError());
        $this->assertEquals('Division by zero is not allowed.', $response->getText());
    }

    /**
     * Create a mock request with the given data.
     */
    private function createMockRequest(array $data): Request
    {
        $request = $this->createMock(Request::class);
        
        // Mock the validate method to return the data as-is for valid operations
        $request->method('validate')->willReturn($data);
        
        return $request;
    }
}

