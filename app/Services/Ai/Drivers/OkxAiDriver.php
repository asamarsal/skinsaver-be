<?php

namespace App\Services\Ai\Drivers;

use Illuminate\Support\Facades\Http;
use Exception;

class OkxAiDriver implements AiDriverInterface
{
    private string $apiKey;
    private string $baseUrl = 'https://ai-api.okx.com/v1'; // Update to the correct OKX.AI API endpoint
    
    public function __construct()
    {
        $this->apiKey = config('ai.okx_api_key');
    }
    
    public function completeJson(string $system, string $user): array
    {
        $response = Http::withToken($this->apiKey)->post("{$this->baseUrl}/chat/completions", [
            'model'    => config('ai.okx_model', 'okx-ai-pro'),
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $user],
            ],
            'response_format' => ['type' => 'json_object'],
        ]);
        
        if ($response->failed()) {
            throw new Exception("OKX.AI completion failed: " . $response->body());
        }

        $jsonStr = $response->json('choices.0.message.content');
        $decoded = json_decode($jsonStr, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("OKX.AI returned invalid JSON: " . $jsonStr);
        }

        return $decoded;
    }
    
    public function supportsVision(): bool
    {
        return true;
    }

    public function completeVision(string $system, string $user, string $imageBase64, string $mimeType): ?string
    {
        $response = Http::withToken($this->apiKey)->post("{$this->baseUrl}/chat/completions", [
            'model' => config('ai.okx_model', 'okx-ai-pro'),
            'messages' => [
                ['role' => 'system', 'content' => $system],
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $user],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:{$mimeType};base64,{$imageBase64}"
                            ]
                        ]
                    ]
                ],
            ],
            'max_tokens' => 1500,
        ]);

        if ($response->failed()) {
            throw new Exception("OKX.AI vision failed: " . $response->body());
        }

        return $response->json('choices.0.message.content');
    }
}
