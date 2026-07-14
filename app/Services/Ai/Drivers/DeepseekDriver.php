<?php

namespace App\Services\Ai\Drivers;

use App\Services\Ai\AiDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepseekDriver implements AiDriverInterface
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://api.deepseek.com';

    public function __construct()
    {
        $this->apiKey = config('ai.deepseek.api_key', '');
        $this->model  = config('ai.deepseek.model', 'deepseek-v4-pro');
    }

    public function complete(string $systemPrompt, string $userPrompt): string
    {
        if (empty($this->apiKey)) {
            Log::warning('[DeepseekDriver] No API key set, returning mock response.');
            return $this->mockFallback($userPrompt);
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(90)
            ->post("{$this->baseUrl}/chat/completions", [
                'model'       => $this->model,
                'temperature' => 0.2,
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $userPrompt],
                ],
            ]);

        if ($response->failed()) {
            Log::error('[DeepseekDriver] API call failed', ['status' => $response->status(), 'body' => $response->body()]);
            return $this->mockFallback($userPrompt);
        }

        return $response->json('choices.0.message.content', '');
    }

    public function completeJson(string $systemPrompt, string $userPrompt): array
    {
        $jsonSystemPrompt = $systemPrompt . "\n\nIMPORTANT: Respond ONLY with valid JSON. No markdown, no explanation.";

        $raw = $this->complete($jsonSystemPrompt, $userPrompt);

        // Strip markdown code fences if present
        $raw = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
        $raw = preg_replace('/\s*```$/', '', $raw);

        return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    }

    public function completeVision(string $systemPrompt, string $userPrompt, string $imageBase64, string $mimeType): ?string
    {
        // Deepseek chat model does not support vision. Return null to signal fallback.
        return null;
    }

    public function supportsVision(): bool
    {
        return false;
    }

    // ─── Fallback mock ──────────────────────────────────────────────────────────
    private function mockFallback(string $userPrompt): string
    {
        return json_encode([
            '_mock'   => true,
            '_reason' => 'Deepseek API key not configured. Using mock data.',
            'hint'    => substr($userPrompt, 0, 80),
        ]);
    }
}
