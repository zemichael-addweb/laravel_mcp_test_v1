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
class TextProcessorTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Processes text with various operations like uppercase, lowercase, reverse, word count, and character count.';

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'text' => 'required|string|max:10000',
            'operation' => 'required|in:uppercase,lowercase,reverse,word_count,char_count,title_case',
        ], [
            'text.required' => 'You must provide text to process.',
            'text.string' => 'The text must be a string.',
            'text.max' => 'The text cannot be longer than 10,000 characters.',
            'operation.required' => 'You must specify an operation.',
            'operation.in' => 'Operation must be one of: uppercase, lowercase, reverse, word_count, char_count, or title_case.',
        ]);

        $text = $validated['text'];
        $operation = $validated['operation'];

        $result = match ($operation) {
            'uppercase' => strtoupper($text),
            'lowercase' => strtolower($text),
            'reverse' => strrev($text),
            'word_count' => str_word_count($text),
            'char_count' => strlen($text),
            'title_case' => ucwords(strtolower($text)),
        };

        if (in_array($operation, ['word_count', 'char_count'])) {
            $label = $operation === 'word_count' ? 'words' : 'characters';
            return Response::text("Text analysis result: {$result} {$label}");
        }

        return Response::text("Processed text ({$operation}):\n\n{$result}");
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'text' => $schema->string()
                ->description('The text to process.')
                ->required(),

            'operation' => $schema->string()
                ->enum(['uppercase', 'lowercase', 'reverse', 'word_count', 'char_count', 'title_case'])
                ->description('The text processing operation to perform.')
                ->required(),
        ];
    }
}
