<?php

namespace App\Services\Ai\Drivers;

use App\Services\Ai\AiDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OpenAI driver — used primarily for GPT-4o Vision when Deepseek cannot process images.
 */
class OpenAiDriver implements AiDriverInterface
{
    private string $apiKey;
    private string $model;
    private string $visionModel;
    private string $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey      = config('ai.openai.api_key', '');
        $this->model       = config('ai.openai.model', 'gpt-4o-mini');
        $this->visionModel = config('ai.openai.vision_model', 'gpt-4o');
    }

    public function complete(string $systemPrompt, string $userPrompt): string
    {
        if (empty($this->apiKey)) {
            Log::warning('[OpenAiDriver] No API key set, returning mock response.');
            return json_encode(['_mock' => true, '_reason' => 'OpenAI API key not configured.']);
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post("{$this->baseUrl}/chat/completions", [
                'model'       => $this->model,
                'temperature' => 0.2,
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $userPrompt],
                ],
            ]);

        if ($response->failed()) {
            Log::error('[OpenAiDriver] API call failed', ['status' => $response->status()]);
            return '';
        }

        return $response->json('choices.0.message.content', '');
    }

    public function completeJson(string $systemPrompt, string $userPrompt): array
    {
        $jsonSystemPrompt = $systemPrompt . "\n\nIMPORTANT: Respond ONLY with valid JSON. No markdown, no explanation.";
        $raw = $this->complete($jsonSystemPrompt, $userPrompt);
        $raw = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
        $raw = preg_replace('/\s*```$/', '', $raw);
        return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    }

    public function completeVision(string $systemPrompt, string $userPrompt, string $imageBase64, string $mimeType): ?string
    {
        if (empty($this->apiKey)) {
            Log::warning('[OpenAiDriver] No API key for vision, returning null.');
            return null;
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(90)
            ->post("{$this->baseUrl}/chat/completions", [
                'model'    => $this->visionModel,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    [
                        'role'    => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $userPrompt],
                            [
                                'type'      => 'image_url',
                                'image_url' => [
                                    'url' => "data:{$mimeType};base64,{$imageBase64}",
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            Log::error('[OpenAiDriver] Vision API call failed', ['status' => $response->status()]);
            return null;
        }

        return $response->json('choices.0.message.content', '');
    }

    public function supportsVision(): bool
    {
        return !empty($this->apiKey);
    }
}
