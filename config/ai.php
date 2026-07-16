<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Text Driver
    |--------------------------------------------------------------------------
    | Supported: "deepseek", "openai"
    | Deepseek is preferred: fast, cheap, and great for structured JSON output.
    */
    'default_driver' => env('AI_ANALYSIS_DRIVER', 'deepseek'),

    /*
    |--------------------------------------------------------------------------
    | Vision Driver (for image/OCR tasks)
    |--------------------------------------------------------------------------
    | Deepseek does not support vision. Use openai (GPT-4o) for image reading.
    */
    'vision_driver' => env('AI_VISION_DRIVER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Deepseek Configuration
    |--------------------------------------------------------------------------
    */
    'deepseek' => [
        'api_key' => env('DEEPSEEK_API_KEY', ''),
        'model'   => env('DEEPSEEK_MODEL', 'deepseek-v4-pro'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    */
    'openai' => [
        'api_key'      => env('OPENAI_API_KEY', ''),
        'model'        => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'vision_model' => env('OPENAI_VISION_MODEL', 'gpt-4o'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OKX.AI Configuration
    |--------------------------------------------------------------------------
    */
    'okx_api_key' => env('OKX_AI_API_KEY', ''),
    'okx_model'   => env('OKX_AI_MODEL', 'okx-ai-pro'),
];
