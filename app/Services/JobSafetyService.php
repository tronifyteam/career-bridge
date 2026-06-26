<?php

namespace App\Services;

use App\Models\Job;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JobSafetyService
{
    private string $model = 'gpt-4o-mini';

    // ── System Prompt ─────────────────────────────────────────────────────────

    private function systemPrompt(string $lang): string
    {
        return "You are a job safety analyst specializing in protecting migrant workers from Taiwan, Indonesia, Vietnam, Thailand, and the Philippines from job scams and exploitation.

Your task is to analyze the provided job or message content and assess its safety risk.

STRICT RULES:
- NEVER say the job is \"100% safe\", \"guaranteed safe\", or \"risk-free\"
- NEVER provide legal guarantees of any kind
- If risk_level is \"high\" or \"critical\", the recommended_action MUST include advice to file a report or seek assistance
- Be specific about what raised concerns — avoid vague generalities
- If information is missing, list what is absent

Return ONLY a valid JSON object with these exact keys:
{
  \"risk_level\": \"low\" | \"medium\" | \"high\" | \"critical\",
  \"risk_reasons\": [\"...\", \"...\"],
  \"missing_info\": [\"...\", \"...\"],
  \"suggested_questions\": [\"...\", \"...\", \"...\"],
  \"recommended_action\": \"...\",
  \"disclaimer\": \"This AI analysis is for informational purposes only and does not constitute legal advice. Always verify job details through official channels.\"
}

Respond in this language: {$lang}";
    }

    // ── OpenAI HTTP helper ─────────────────────────────────────────────────────

    private function callOpenAI(array $messages, string $lang): ?array
    {
        $apiKey = config('services.openai.api_key');
        if (empty($apiKey)) {
            Log::warning('JobSafetyService: OPENAI_API_KEY is not set.');
            return null;
        }

        try {
            $request = Http::withToken($apiKey)->timeout(30);
            
            if (config('services.openai.use_proxy', false)) {
                $request = $request->withOptions(['proxy' => config('services.openai.proxy_url')]);
            }

            $response = $request->post('https://api.openai.com/v1/chat/completions', [
                'model'           => $this->model,
                'messages'        => $messages,
                'temperature'     => 0.3,
                'response_format' => ['type' => 'json_object'],
            ]);

            if ($response->successful()) {
                $data    = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                $json    = json_decode($content, true);

                if ($json && isset($json['risk_level'])) {
                    return $json;
                }
            }

            Log::error('JobSafetyService API Error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('JobSafetyService Exception: ' . $e->getMessage());
            return null;
        }
    }

    // ── Public Methods ─────────────────────────────────────────────────────────

    /**
     * Analyze a job listing from the database.
     */
    public function analyzeJob(Job $job, string $lang = 'en'): ?array
    {
        $jobText = implode("\n", array_filter([
            "Title: {$job->title}",
            "Employer: {$job->employer_name} ({$job->employer_type})",
            "Location: {$job->location}",
            "Salary: {$job->salary} / {$job->salary_period}",
            "Employment Type: {$job->employment_type}",
            "Working Hours: {$job->working_hours_and_rest_days}",
            "Worker Count: {$job->worker_count}",
            "Employment Period: {$job->employment_period}",
            "Dormitory/Meals/Deductions: {$job->dormitory_meals_deductions}",
            "Contact Method: " . ($job->mask_contact_info ? '[Hidden — contact via platform]' : $job->contact_method),
            "Description: {$job->description}",
            "Duties: {$job->duties}",
            "Requirements: {$job->requirements}",
            "Benefits: {$job->benefits}",
            "Legal Status: {$job->legal_status}",
            "Eligibility: {$job->eligibility}",
        ]));

        return $this->callOpenAI([
            ['role' => 'system', 'content' => $this->systemPrompt($lang)],
            ['role' => 'user',   'content' => "Please analyze this job listing for safety risks:\n\n{$jobText}"],
        ], $lang);
    }

    /**
     * Analyze a list of chat messages from an employer.
     */
    public function analyzeMessages(array $messages, string $lang = 'en'): ?array
    {
        $messagesText = implode("\n", array_map(
            fn($i, $m) => "Message " . ($i + 1) . ": {$m}",
            array_keys($messages),
            $messages
        ));

        return $this->callOpenAI([
            ['role' => 'system', 'content' => $this->systemPrompt($lang)],
            ['role' => 'user',   'content' => "Please analyze these employer messages for safety risks:\n\n{$messagesText}"],
        ], $lang);
    }

    /**
     * Analyze an uploaded screenshot image (base64 encoded).
     * Uses GPT-4o vision capability.
     */
    public function analyzeScreenshot(string $base64Image, string $mimeType = 'image/jpeg', string $lang = 'en'): ?array
    {
        $apiKey = config('services.openai.api_key');
        if (empty($apiKey)) {
            Log::warning('JobSafetyService: OPENAI_API_KEY is not set.');
            return null;
        }

        try {
            $request = Http::withToken($apiKey)->timeout(45);
            
            if (config('services.openai.use_proxy', false)) {
                $request = $request->withOptions(['proxy' => config('services.openai.proxy_url')]);
            }

            $response = $request->post('https://api.openai.com/v1/chat/completions', [
                'model'           => 'gpt-4o', // Vision requires gpt-4o
                'messages'        => [
                        ['role' => 'system', 'content' => $this->systemPrompt($lang)],
                        [
                            'role'    => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => 'Please analyze this screenshot of a job advertisement or employer message for safety risks:',
                                ],
                                [
                                    'type'      => 'image_url',
                                    'image_url' => [
                                        'url'    => "data:{$mimeType};base64,{$base64Image}",
                                        'detail' => 'low',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'temperature'     => 0.3,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if ($response->successful()) {
                $data    = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                $json    = json_decode($content, true);

                if ($json && isset($json['risk_level'])) {
                    return $json;
                }
            }

            Log::error('JobSafetyService Screenshot Error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('JobSafetyService Screenshot Exception: ' . $e->getMessage());
            return null;
        }
    }
}
