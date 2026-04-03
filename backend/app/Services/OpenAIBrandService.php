<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIBrandService
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
You are identifying the brand on a beverage cup. Look at the logo, text, and design.

Known brands:
- STARBUCKS: Green siren/mermaid logo, "STARBUCKS" text, clear plastic cups with measurement markings
- ZUS COFFEE: Zeus figure illustration (Greek god with staff), "ZUS COFFEE" text, navy blue paper cups or clear plastic
- CHAGEE: Two designs — (1) navy blue constellation/floral with "CHAGEE" diagonal text, (2) beige/nature with crane and green accents. Red CHAGEE stamp. Paper hot cups.
- LUCKY CUP: Red paper cup with "LUCKY CUP" white text, 8-ball graphic. Plastic cup has red shield/crest with king illustration, Chinese characters.
- MIXUE: Bright red and white branding, cartoon snowman mascot ("Snow King"), "MIXUE" or "蜜雪冰城" text, plastic cups with red logo.

Respond with ONLY one word — the brand slug: starbucks, zus, chagee, luckycup, mixue, or unknown
PROMPT;

    private const VALID_SLUGS = ['starbucks', 'zus', 'chagee', 'luckycup', 'mixue', 'unknown'];

    public function detectBrand(string $base64Image): ?string
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer '.config('services.openai.api_key'),
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o',
                    'max_tokens' => 10,
                    'messages' => [
                        ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                        ['role' => 'user', 'content' => [
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => 'data:image/jpeg;base64,'.$base64Image,
                                    'detail' => 'low',
                                ],
                            ],
                        ]],
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('OpenAI brand detection failed', ['status' => $response->status()]);

                return null;
            }

            $content = strtolower(trim($response->json('choices.0.message.content', 'unknown')));

            return in_array($content, self::VALID_SLUGS) ? $content : 'unknown';
        } catch (\Exception $e) {
            Log::error('OpenAI brand detection error', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
