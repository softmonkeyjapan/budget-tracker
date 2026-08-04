<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

final class FeedbackClassificationService
{
    private const TYPES = ['bug', 'enhancement', 'question'];

    /**
     * @return array{type: string, title: string}|null
     */
    public function classify(string $message): ?array
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => config('services.anthropic.key'),
                'anthropic-version' => '2023-06-01',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-haiku-4-5-20251001',
                'max_tokens' => 256,
                'output_config' => [
                    'format' => [
                        'type' => 'json_schema',
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'type' => ['type' => 'string', 'enum' => self::TYPES],
                                'title' => ['type' => 'string'],
                            ],
                            'required' => ['type', 'title'],
                            'additionalProperties' => false,
                        ],
                    ],
                ],
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Classify this user feedback as bug, enhancement, or question, and generate a short, descriptive title (max 80 characters):\n\n{$message}",
                    ],
                ],
            ]);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $text = $response->json('content.0.text');
        $decoded = is_string($text) ? json_decode($text, true) : null;

        if (
            ! is_array($decoded)
            || ! in_array($decoded['type'] ?? null, self::TYPES, true)
            || ! is_string($decoded['title'] ?? null)
            || trim($decoded['title']) === ''
        ) {
            return null;
        }

        return ['type' => $decoded['type'], 'title' => $decoded['title']];
    }
}
