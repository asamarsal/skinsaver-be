<?php

namespace App\Services\Ai;

use App\Services\Ai\Drivers\DeepseekDriver;
use App\Services\Ai\Drivers\OpenAiDriver;
use App\Services\Ai\Drivers\OkxAiDriver;
use InvalidArgumentException;

/**
 * AiServiceManager — Multi-LLM Router
 *
 * Usage:
 *   app(AiServiceManager::class)->driver()            // returns default driver (deepseek)
 *   app(AiServiceManager::class)->driver('openai')    // returns OpenAI driver
 *   app(AiServiceManager::class)->visionDriver()      // returns best available vision driver
 */
class AiServiceManager
{
    /** @var array<string, AiDriverInterface> */
    private array $resolved = [];

    /**
     * Get a specific driver by name, or the configured default.
     */
    public function driver(?string $name = null): AiDriverInterface
    {
        $name = $name ?? config('ai.default_driver', 'deepseek');

        if (!isset($this->resolved[$name])) {
            $this->resolved[$name] = $this->make($name);
        }

        return $this->resolved[$name];
    }

    /**
     * Returns the best available driver that supports vision/image input.
     * Falls back to a mock-aware driver if none is configured.
     */
    public function visionDriver(): AiDriverInterface
    {
        $preferred = config('ai.vision_driver', 'openai');
        $driver    = $this->driver($preferred);

        if ($driver->supportsVision()) {
            return $driver;
        }

        // Try openai as fallback
        $openai = $this->driver('openai');
        if ($openai->supportsVision()) {
            return $openai;
        }

        // No vision-capable driver is configured; return the default driver
        // (it will return null from completeVision, handled by callers)
        return $this->driver();
    }

    private function make(string $name): AiDriverInterface
    {
        return match ($name) {
            'deepseek' => new DeepseekDriver(),
            'openai'   => new OpenAiDriver(),
            'okx_ai'   => new OkxAiDriver(),
            default    => throw new InvalidArgumentException("Unknown AI driver: [{$name}]"),
        };
    }
}
