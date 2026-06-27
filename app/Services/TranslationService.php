<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    /**
     * Translates the given message to the target language using OpenAI API.
     *
     * @param string $message The original message.
     * @param string $targetLanguage The preferred language of the receiver (e.g., 'id', 'zh-TW').
     * @return array|null An array containing ['text' => '...', 'detected_language' => '...'] or null if it fails.
     */
    public function translate(string $message, string $targetLanguage): ?array
    {
        $apiKey = config('services.openai.api_key');
        if (empty($apiKey)) {
            Log::warning('TranslationService: OPENAI_API_KEY is not set.');
            return null;
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(25)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "You are a professional translator. Your task is to detect the source language of the given text and translate it into '$targetLanguage'. Return ONLY a JSON object with two keys: \"translated_text\" (string) and \"detected_language_code\" (ISO 639-1 code string, e.g., 'en', 'id', 'zh-TW'). Do not include any markdown formatting or extra text."
                        ],
                        [
                            'role' => 'user',
                            'content' => $message
                        ]
                    ],
                    'temperature' => 0.3,
                    'response_format' => ['type' => 'json_object']
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                $json = json_decode($content, true);

                if ($json && isset($json['translated_text'])) {
                    return [
                        'text' => trim($json['translated_text']),
                        'detected_language' => $json['detected_language_code'] ?? null,
                    ];
                }
            }

            Log::error('TranslationService API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return null;

        } catch (\Exception $e) {
            Log::error('TranslationService Exception: ' . $e->getMessage());
            return null;
        }
    }
}
