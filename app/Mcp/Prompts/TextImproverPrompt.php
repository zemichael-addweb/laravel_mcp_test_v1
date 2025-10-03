<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

class TextImproverPrompt extends Prompt
{
    /**
     * The prompt's name.
     */
    protected string $name = 'text-improver';

    /**
     * The prompt's title.
     */
    protected string $title = 'Text Improvement Assistant';

    /**
     * The prompt's description.
     */
    protected string $description = 'Improves text quality by enhancing clarity, grammar, style, and readability based on specified criteria.';

    /**
     * Handle the prompt request.
     *
     * @return array<int, \Laravel\Mcp\Response>
     */
    public function handle(Request $request): array
    {
        $validated = $request->validate([
            'text' => 'required|string|max:5000',
            'improvement_type' => 'in:grammar,clarity,style,professional,casual,academic,creative',
            'target_audience' => 'string|max:100',
            'preserve_length' => 'boolean',
            'explain_changes' => 'boolean',
        ], [
            'text.required' => 'You must provide text to improve.',
            'text.max' => 'Text cannot be longer than 5,000 characters.',
            'improvement_type.in' => 'Improvement type must be one of: grammar, clarity, style, professional, casual, academic, or creative.',
            'target_audience.max' => 'Target audience description cannot be longer than 100 characters.',
        ]);

        $text = $validated['text'];
        $improvementType = $validated['improvement_type'] ?? 'clarity';
        $targetAudience = $validated['target_audience'] ?? 'general audience';
        $preserveLength = $validated['preserve_length'] ?? false;
        $explainChanges = $validated['explain_changes'] ?? true;

        $systemMessage = $this->buildSystemMessage($improvementType, $targetAudience, $preserveLength, $explainChanges);
        $userMessage = "Please improve the following text:\n\n{$text}";

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
                name: 'text',
                description: 'The text to improve.',
                required: true,
            ),
            new Argument(
                name: 'improvement_type',
                description: 'Type of improvement: grammar, clarity, style, professional, casual, academic, or creative.',
                required: false,
            ),
            new Argument(
                name: 'target_audience',
                description: 'The intended audience for the improved text (e.g., "business professionals", "students").',
                required: false,
            ),
            new Argument(
                name: 'preserve_length',
                description: 'Whether to keep the text approximately the same length.',
                required: false,
            ),
            new Argument(
                name: 'explain_changes',
                description: 'Whether to explain what changes were made and why.',
                required: false,
            ),
        ];
    }

    /**
     * Build the system message based on the request parameters.
     */
    private function buildSystemMessage(string $improvementType, string $targetAudience, bool $preserveLength, bool $explainChanges): string
    {
        $improvementDescriptions = [
            'grammar' => 'Fix grammatical errors, punctuation, and sentence structure',
            'clarity' => 'Enhance clarity and readability while maintaining the original meaning',
            'style' => 'Improve writing style, flow, and engagement',
            'professional' => 'Make the text more professional and business-appropriate',
            'casual' => 'Make the text more conversational and approachable',
            'academic' => 'Enhance the text for academic or scholarly purposes',
            'creative' => 'Add creative flair and engaging language while preserving the core message',
        ];

        $message = "You are a professional editor and writing coach. ";
        $message .= "Your task is to {$improvementDescriptions[$improvementType]} ";
        $message .= "for a {$targetAudience}. ";

        if ($preserveLength) {
            $message .= "Keep the revised text approximately the same length as the original. ";
        } else {
            $message .= "You may adjust the length as needed to improve the text quality. ";
        }

        $message .= "Focus on maintaining the original tone and intent while making the specified improvements. ";

        if ($explainChanges) {
            $message .= "After providing the improved text, briefly explain the key changes you made and why they improve the text. ";
        } else {
            $message .= "Provide only the improved text without explanations. ";
        }

        return $message;
    }
}
