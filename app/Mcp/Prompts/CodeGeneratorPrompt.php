<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

class CodeGeneratorPrompt extends Prompt
{
    /**
     * The prompt's name.
     */
    protected string $name = 'code-generator';

    /**
     * The prompt's title.
     */
    protected string $title = 'Code Generator Assistant';

    /**
     * The prompt's description.
     */
    protected string $description = 'Generates code snippets based on specified programming language, functionality, and style preferences.';

    /**
     * Handle the prompt request.
     *
     * @return array<int, \Laravel\Mcp\Response>
     */
    public function handle(Request $request): array
    {
        $validated = $request->validate([
            'language' => 'required|string|max:50',
            'functionality' => 'required|string|max:500',
            'style' => 'in:beginner,intermediate,advanced,production',
            'include_comments' => 'boolean',
            'include_tests' => 'boolean',
        ], [
            'language.required' => 'You must specify a programming language (e.g., "PHP", "JavaScript", "Python").',
            'functionality.required' => 'You must describe what functionality you want the code to implement.',
            'functionality.max' => 'Functionality description cannot be longer than 500 characters.',
            'style.in' => 'Style must be one of: beginner, intermediate, advanced, or production.',
        ]);

        $language = $validated['language'];
        $functionality = $validated['functionality'];
        $style = $validated['style'] ?? 'intermediate';
        $includeComments = $validated['include_comments'] ?? true;
        $includeTests = $validated['include_tests'] ?? false;

        $systemMessage = $this->buildSystemMessage($language, $style, $includeComments, $includeTests);
        $userMessage = "Please generate {$language} code that implements the following functionality: {$functionality}";

        return [
            Response::text($systemMessage)->asAssistant(),
            Response::text($userMessage),
        ];
    }

    /**
     * Get the prompt's arguments.
     *
     * @return array<int, \Laravel\Mcp\Server\Prompts\Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument(
                name: 'language',
                description: 'The programming language to generate code for (e.g., PHP, JavaScript, Python).',
                required: true,
            ),
            new Argument(
                name: 'functionality',
                description: 'Description of what functionality the code should implement.',
                required: true,
            ),
            new Argument(
                name: 'style',
                description: 'The complexity/style level: beginner, intermediate, advanced, or production.',
                required: false,
            ),
            new Argument(
                name: 'include_comments',
                description: 'Whether to include explanatory comments in the code.',
                required: false,
            ),
            new Argument(
                name: 'include_tests',
                description: 'Whether to include unit tests for the generated code.',
                required: false,
            ),
        ];
    }

    /**
     * Build the system message based on the request parameters.
     */
    private function buildSystemMessage(string $language, string $style, bool $includeComments, bool $includeTests): string
    {
        $styleDescriptions = [
            'beginner' => 'simple, easy-to-understand code with clear variable names and basic patterns',
            'intermediate' => 'well-structured code using appropriate design patterns and best practices',
            'advanced' => 'sophisticated code utilizing advanced language features and optimizations',
            'production' => 'enterprise-ready code with error handling, logging, security considerations, and comprehensive documentation',
        ];

        $message = "You are a senior software engineer specialized in {$language}. ";
        $message .= "Generate {$styleDescriptions[$style]}. ";

        if ($includeComments) {
            $message .= "Include clear, helpful comments explaining the code's purpose and key logic. ";
        } else {
            $message .= "Focus on clean, self-documenting code without extensive comments. ";
        }

        if ($includeTests) {
            $message .= "Also provide unit tests for the generated code using appropriate testing frameworks for {$language}. ";
        }

        $message .= "Ensure the code follows {$language} conventions and best practices. ";
        $message .= "Format the code properly with appropriate indentation and structure.";

        return $message;
    }
}
