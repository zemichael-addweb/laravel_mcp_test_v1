<?php

namespace App\Mcp\Tools;

use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[IsReadOnly]
#[IsIdempotent]
class CalculatorTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Performs basic mathematical operations including addition, subtraction, multiplication, and division.';

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'operation' => 'required|in:add,subtract,multiply,divide',
            'a' => 'required|numeric',
            'b' => 'required|numeric',
        ], [
            'operation.required' => 'You must specify an operation: add, subtract, multiply, or divide.',
            'operation.in' => 'Operation must be one of: add, subtract, multiply, or divide.',
            'a.required' => 'You must provide the first number (a).',
            'a.numeric' => 'The first number (a) must be numeric.',
            'b.required' => 'You must provide the second number (b).',
            'b.numeric' => 'The second number (b) must be numeric.',
        ]);

        $operation = $validated['operation'];
        $a = floatval($validated['a']);
        $b = floatval($validated['b']);

        $result = match ($operation) {
            'add' => $a + $b,
            'subtract' => $a - $b,
            'multiply' => $a * $b,
            'divide' => $b != 0 ? $a / $b : null,
        };

        if ($result === null) {
            return Response::error('Division by zero is not allowed.');
        }

        return Response::text("Result: {$a} {$this->getOperationSymbol($operation)} {$b} = {$result}");
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
                ->enum(['add', 'subtract', 'multiply', 'divide'])
                ->description('The mathematical operation to perform.')
                ->required(),

            'a' => $schema->number()
                ->description('The first number in the operation.')
                ->required(),

            'b' => $schema->number()
                ->description('The second number in the operation.')
                ->required(),
        ];
    }

    /**
     * Get the operation symbol for display.
     */
    private function getOperationSymbol(string $operation): string
    {
        return match ($operation) {
            'add' => '+',
            'subtract' => '-',
            'multiply' => '×',
            'divide' => '÷',
        };
    }
}
