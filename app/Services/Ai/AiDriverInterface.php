<?php

namespace App\Services\Ai;

interface AiDriverInterface
{
    /**
     * Send a text-based prompt to the AI and return the response string.
     */
    public function complete(string $systemPrompt, string $userPrompt): string;

    /**
     * Send a text-based prompt and return parsed JSON array.
     * Throws \JsonException on invalid JSON.
     */
    public function completeJson(string $systemPrompt, string $userPrompt): array;

    /**
     * Send a vision (image + text) prompt and return the response string.
     * $imageBase64 should be a base64-encoded image string.
     * $mimeType should be e.g. 'image/jpeg', 'image/png'.
     * Returns null if the driver does not support vision.
     */
    public function completeVision(string $systemPrompt, string $userPrompt, string $imageBase64, string $mimeType): ?string;

    /**
     * Returns whether this driver supports image/vision input.
     */
    public function supportsVision(): bool;
}
