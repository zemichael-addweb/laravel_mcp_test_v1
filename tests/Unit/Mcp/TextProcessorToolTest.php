<?php

namespace Tests\Unit\Mcp;

use App\Mcp\Tools\TextProcessorTool;
use Laravel\Mcp\Request;
use PHPUnit\Framework\TestCase;

class TextProcessorToolTest extends TestCase
{
    private TextProcessorTool $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tool = new TextProcessorTool();
    }

    /**
     * Test uppercase operation.
     */
    public function test_uppercase_operation()
    {
        $request = $this->createMockRequest([
            'text' => 'hello world',
            'operation' => 'uppercase'
        ]);

        $response = $this->tool->handle($request);
        
        $this->assertStringContainsString('HELLO WORLD', $response->getText());
    }

    /**
     * Test lowercase operation.
     */
    public function test_lowercase_operation()
    {
        $request = $this->createMockRequest([
            'text' => 'HELLO WORLD',
            'operation' => 'lowercase'
        ]);

        $response = $this->tool->handle($request);
        
        $this->assertStringContainsString('hello world', $response->getText());
    }

    /**
     * Test reverse operation.
     */
    public function test_reverse_operation()
    {
        $request = $this->createMockRequest([
            'text' => 'hello',
            'operation' => 'reverse'
        ]);

        $response = $this->tool->handle($request);
        
        $this->assertStringContainsString('olleh', $response->getText());
    }

    /**
     * Test word count operation.
     */
    public function test_word_count_operation()
    {
        $request = $this->createMockRequest([
            'text' => 'The quick brown fox jumps',
            'operation' => 'word_count'
        ]);

        $response = $this->tool->handle($request);
        
        $this->assertStringContainsString('5 words', $response->getText());
    }

    /**
     * Test character count operation.
     */
    public function test_char_count_operation()
    {
        $request = $this->createMockRequest([
            'text' => 'hello',
            'operation' => 'char_count'
        ]);

        $response = $this->tool->handle($request);
        
        $this->assertStringContainsString('5 characters', $response->getText());
    }

    /**
     * Test title case operation.
     */
    public function test_title_case_operation()
    {
        $request = $this->createMockRequest([
            'text' => 'hello world test',
            'operation' => 'title_case'
        ]);

        $response = $this->tool->handle($request);
        
        $this->assertStringContainsString('Hello World Test', $response->getText());
    }

    /**
     * Create a mock request with the given data.
     */
    private function createMockRequest(array $data): Request
    {
        $request = $this->createMock(Request::class);
        
        // Mock the validate method to return the data as-is
        $request->method('validate')->willReturn($data);
        
        return $request;
    }
}

