<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\SafetyCheck;
use App\Services\JobSafetyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SafetyCheckerController extends Controller
{
    /**
     * POST /api/ai/safety-check/job/{jobId}
     * Analyze a job listing for safety risks.
     */
    public function checkJob(Request $request, string $jobId): JsonResponse
    {
        $user = $request->user();
        $job  = Job::find($jobId);

        if (!$job) {
            return response()->json([
                'success' => false,
                'error'   => 'not_found',
                'message' => 'Job not found.',
            ], 404);
        }

        $lang    = $this->resolveLanguage($request, $user);
        $service = app(JobSafetyService::class);
        $result  = $service->analyzeJob($job, $lang);

        if (!$result) {
            return $this->analysisFailedResponse();
        }

        $this->saveCheck($user->id, 'job', (string) $jobId, null, null, $result, $lang);

        return response()->json([
            'success' => true,
            'data'    => $this->formatResult($result),
        ]);
    }

    /**
     * POST /api/ai/safety-check/messages
     * Analyze an array of employer chat messages for safety risks.
     *
     * Body: { "messages": ["msg1", "msg2", ...] }
     */
    public function checkMessages(Request $request): JsonResponse
    {
        $request->validate([
            'messages'   => 'required|array|min:1|max:50',
            'messages.*' => 'required|string|max:2000',
        ]);

        $user     = $request->user();
        $messages = $request->input('messages');
        $lang     = $this->resolveLanguage($request, $user);

        $service = app(JobSafetyService::class);
        $result  = $service->analyzeMessages($messages, $lang);

        if (!$result) {
            return $this->analysisFailedResponse();
        }

        $inputText = implode(' | ', $messages);
        $this->saveCheck($user->id, 'chat', null, $inputText, null, $result, $lang);

        return response()->json([
            'success' => true,
            'data'    => $this->formatResult($result),
        ]);
    }

    /**
     * POST /api/ai/safety-check/screenshot
     * Analyze an uploaded screenshot for safety risks.
     *
     * Body: multipart/form-data with field "image" (JPEG/PNG, max 5MB)
     */
    public function checkScreenshot(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $user  = $request->user();
        $lang  = $this->resolveLanguage($request, $user);
        $file  = $request->file('image');

        // Store temporarily, read as base64
        $path      = $file->store('safety_screenshots', 'public');
        $fullPath  = Storage::disk('public')->path($path);
        $base64    = base64_encode(file_get_contents($fullPath));
        $mimeType  = $file->getMimeType();

        $service = app(JobSafetyService::class);
        $result  = $service->analyzeScreenshot($base64, $mimeType, $lang);

        // Build public URL for storage record
        $imageUrl = Storage::disk('public')->url($path);

        if (!$result) {
            // Clean up the stored file on failure
            Storage::disk('public')->delete($path);
            return $this->analysisFailedResponse();
        }

        $this->saveCheck($user->id, 'screenshot', null, null, $imageUrl, $result, $lang);

        // Clean up temp file — we only keep the URL reference in the DB
        Storage::disk('public')->delete($path);

        return response()->json([
            'success' => true,
            'data'    => $this->formatResult($result),
        ]);
    }

    /**
     * GET /api/ai/safety-check/history
     * Get the current user's safety check history (last 20).
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        $checks = SafetyCheck::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn($c) => [
                'id'          => (string) $c->id,
                'source_type' => $c->source_type,
                'source_id'   => $c->source_id,
                'risk_level'  => $c->risk_level,
                'image_url'   => $c->image_url,
                'created_at'  => $c->created_at->toIso8601String(),
                'result'      => $c->result_json,
            ]);

        return response()->json([
            'success' => true,
            'data'    => $checks,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resolveLanguage(Request $request, $user): string
    {
        // Priority: explicit param > user preferred_language > 'en'
        $lang = $request->input('lang')
            ?? $user->preferred_language
            ?? 'en';

        // Normalize to OpenAI-friendly string
        $map = [
            'id'    => 'Indonesian',
            'en'    => 'English',
            'zh'    => 'Traditional Chinese',
            'zh-tw' => 'Traditional Chinese',
            'vi'    => 'Vietnamese',
            'th'    => 'Thai',
            'tl'    => 'Filipino (Tagalog)',
            'ja'    => 'Japanese',
        ];

        return $map[strtolower($lang)] ?? 'English';
    }

    private function saveCheck(
        int     $userId,
        string  $sourceType,
        ?string $sourceId,
        ?string $inputText,
        ?string $imageUrl,
        array   $result,
        string  $lang
    ): void {
        try {
            SafetyCheck::create([
                'user_id'     => $userId,
                'source_type' => $sourceType,
                'source_id'   => $sourceId,
                'input_text'  => $inputText ? mb_substr($inputText, 0, 5000) : null,
                'image_url'   => $imageUrl,
                'risk_level'  => $result['risk_level'],
                'result_json' => $result,
                'language'    => $lang,
            ]);
        } catch (\Exception $e) {
            Log::warning('SafetyCheckerController: Failed to save check history — ' . $e->getMessage());
        }
    }

    private function formatResult(array $result): array
    {
        return [
            'risk_level'          => $result['risk_level']          ?? 'unknown',
            'risk_reasons'        => $result['risk_reasons']        ?? [],
            'missing_info'        => $result['missing_info']        ?? [],
            'suggested_questions' => $result['suggested_questions'] ?? [],
            'recommended_action'  => $result['recommended_action']  ?? '',
            'disclaimer'          => $result['disclaimer']          ?? 'This analysis is for informational purposes only.',
        ];
    }

    private function analysisFailedResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error'   => 'analysis_failed',
            'message' => 'AI safety analysis could not be completed. Please try again.',
        ], 502);
    }
}
