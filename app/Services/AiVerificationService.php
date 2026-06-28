<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AiVerificationService
{
    private string $model = 'gpt-4o';

    /**
     * Compare a selfie and an ID document using OpenAI Vision.
     * Returns an array with results, or null on failure.
     * 
     * @param string $selfiePath Local storage path (e.g., from file_url)
     * @param string $ktpPath Local storage path
     * @return array|null
     */
    public function verifyIdentity(string $selfiePath, string $ktpPath): ?array
    {
        $apiKey = config('services.openai.api_key');
        if (empty($apiKey)) {
            Log::warning('AiVerificationService: OPENAI_API_KEY is not set.');
            return null;
        }

        try {
            // Read files and convert to base64
            $selfieBase64 = $this->getBase64Image($selfiePath);
            $ktpBase64 = $this->getBase64Image($ktpPath);

            if (!$selfieBase64 || !$ktpBase64) {
                Log::warning('AiVerificationService: Could not read image files.');
                return null;
            }

            $messages = [
                [
                    'role' => 'system',
                    'content' => 'You are an AI Identity Verification Agent. Your task is to verify if the person in the selfie matches the person in the ID document (KTP, Passport, or ARC). Also check if the ID document looks like a valid official document.'
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "Please analyze these two images. Image 1 is a selfie, Image 2 is an ID document.\n\nReturn ONLY a JSON object with these exact keys:\n- \"is_match\" (boolean): true if the faces match, false otherwise.\n- \"is_valid_id\" (boolean): true if Image 2 appears to be a valid official ID document.\n- \"extracted_name\" (string|null): The name extracted from the ID document, if readable.\n- \"reason\" (string): A brief explanation of your decision (e.g., 'Faces match and ID is valid', or 'Faces do not match')."
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $selfieBase64
                            ]
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $ktpBase64
                            ]
                        ]
                    ]
                ]
            ];

            $request = Http::withToken($apiKey)->timeout(60);
            
            if (config('services.openai.use_proxy', false)) {
                $request = $request->withOptions(['proxy' => config('services.openai.proxy_url')]);
            }

            $response = $request->post('https://api.openai.com/v1/chat/completions', [
                'model'           => $this->model,
                'messages'        => $messages,
                'temperature'     => 0.1,
                'max_tokens'      => 300,
                'response_format' => ['type' => 'json_object'],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                $json = json_decode($content, true);

                if ($json && isset($json['is_match'])) {
                    return $json;
                }
            }

            Log::error('AiVerificationService API Error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('AiVerificationService Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Convert local storage path/url to Base64 data URI
     */
    private function getBase64Image(string $pathOrUrl): ?string
    {
        try {
            $path = $pathOrUrl;
            // If it's a full URL (e.g., asset('storage/...')), extract the relative path
            if (str_starts_with($pathOrUrl, 'http')) {
                // Find 'storage/' and get everything after it
                $pos = strpos($pathOrUrl, 'storage/');
                if ($pos !== false) {
                    $path = substr($pathOrUrl, $pos + 8);
                }
            }

            if (!Storage::disk('public')->exists($path)) {
                // Fallback for absolute local paths or direct web fetch?
                // For this project, files are in storage/app/public/
                return null;
            }

            $contents = Storage::disk('public')->get($path);
            $mime = Storage::disk('public')->mimeType($path);
            $base64 = base64_encode($contents);

            return "data:{$mime};base64,{$base64}";
        } catch (\Exception $e) {
            Log::error('AiVerificationService getBase64Image Error: ' . $e->getMessage());
            return null;
        }
    }
}
