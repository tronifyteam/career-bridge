<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\EmployerStaffController;
use App\Http\Controllers\Api\JobApplicationController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\UserOnboardingController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\WorkerDirectoryController;
use App\Http\Controllers\Api\WorkerDocumentController;
use App\Http\Controllers\Api\WorkerStatusController;
use App\Http\Controllers\Admin\AdminWorkerController;
use App\Http\Controllers\Api\AdvertisementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — 2ne5 Migrant Work Platform  (v2.1.0)
|--------------------------------------------------------------------------
| All responses follow: { success, data?, error?, message? }
|--------------------------------------------------------------------------
*/

// ── Health Check ─────────────────────────────────────────────────────────
Route::get('/health', fn() => response()->json([
    'success' => true,
    'data' => [
        'status'  => 'ok',
        'version' => '2.1.0',
        'time'    => now()->toIso8601String(),
    ],
]));

// ── Master Data (Public) ───────────────────────────────────────7───────────
Route::prefix('meta')->group(function () {
    Route::get('/worker-types', [UserOnboardingController::class, 'workerTypes']);
    Route::get('/languages',    [WorkerStatusController::class,    'listLanguages']);
    Route::get('/job-types',    [WorkerStatusController::class,    'listJobTypes']);
    Route::get('/nationalities',[\App\Http\Controllers\Api\MasterDataController::class, 'nationalities']);
});

Route::get('/categories', [\App\Http\Controllers\Api\CategoryController::class, 'categories']);
Route::get('/cities',     [\App\Http\Controllers\Api\CategoryController::class, 'cities']);
Route::get('/skills',     [\App\Http\Controllers\Api\MasterDataController::class, 'skills']);
Route::get('/industries', [\App\Http\Controllers\Api\MasterDataController::class, 'industries']);
Route::get('/nationalities', [\App\Http\Controllers\Api\MasterDataController::class, 'nationalities']);

// ── Authentication ────────────────────────────────────────────────────────
// Rate limit: 5 attempts per minute per IP for login (brute force protection)
Route::middleware('throttle:5,1')->prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/google',   [AuthController::class, 'googleLogin']);
    Route::post('/check-email', [AuthController::class, 'checkEmail']);
    Route::post('/send-email-otp', [AuthController::class, 'sendEmailOtp']);
    Route::post('/verify-email-otp', [AuthController::class, 'verifyEmailOtp']);
    Route::post('/logout',   [AuthController::class, 'logout'])->middleware('auth:sanctum');

    // Password reset
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password',  [AuthController::class, 'resetPassword']);

    // Authenticated profile routes
    // Advertisements (Public / Any User)
Route::get('/ads/banners', [AdvertisementController::class, 'getBanners']);
Route::post('/ads/{id}/click', [AdvertisementController::class, 'trackClick'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me',              [AuthController::class, 'me']);
        Route::put('/profile',         [AuthController::class, 'saveProfile']);
        Route::put('/role',            [AuthController::class, 'updateRole']);
        Route::post('/avatar',         [AuthController::class, 'uploadAvatar']);
        Route::delete('/avatar',       [AuthController::class, 'deleteAvatar']);
        Route::post('/cv',             [AuthController::class, 'uploadCv']);
        Route::delete('/cv',           [AuthController::class, 'deleteCv']);   // fix: was /api/auth/cv in mobile
        Route::post('/fcm-token',      [AuthController::class, 'updateFcmToken']);
        Route::put('/notification-preferences', [AuthController::class, 'updateNotificationPreferences']);

        // Employer document upload (old flow — kept for compatibility)
        Route::post('/employer/document', [AuthController::class, 'uploadDocument']);
        Route::post('/worker/document',   [AuthController::class, 'uploadWorkerDocument']);

        // ── Mobile Compatibility Aliases ─────────────────────────────────
        // Mobile calls /auth/email/* and /auth/phone/* instead of /verify/*
        Route::post('/email/send-code',  [VerificationController::class, 'sendEmailCode']);
        Route::post('/email/verify',     [VerificationController::class, 'verifyEmail']);
        Route::post('/phone/send-otp',   [VerificationController::class, 'sendPhoneOtp']);
        Route::post('/phone/verify-otp', [VerificationController::class, 'verifyPhoneOtp']);
        Route::post('/phone/verify-firebase', [VerificationController::class, 'verifyFirebasePhone']);
    });
});

// ── Verification (canonical new routes) ──────────────────────────────────
Route::middleware('auth:sanctum')->prefix('verify')->group(function () {
    Route::post('/email/send',    [VerificationController::class, 'sendEmailCode']);
    Route::post('/email/confirm', [VerificationController::class, 'verifyEmail']);
    Route::post('/phone/send',    [VerificationController::class, 'sendPhoneOtp']);
    Route::post('/phone/confirm', [VerificationController::class, 'verifyPhoneOtp']);
});

// ── Onboarding Flow (ALUR.jpg — 6 Steps) ─────────────────────────────────
Route::middleware('auth:sanctum')->prefix('onboarding')->group(function () {
    Route::get('/status',               [UserOnboardingController::class, 'status']);
    Route::post('/selfie',              [UserOnboardingController::class, 'uploadSelfie']);           // Step 3 (all users)
    Route::post('/role',                [UserOnboardingController::class, 'selectRole']);              // Step 4 — @deprecated, role now set implicitly by /worker-type or /employer-category
    Route::post('/worker-type',         [UserOnboardingController::class, 'selectWorkerType']);        // Step 5W
    Route::post('/employer-category',   [UserOnboardingController::class, 'selectEmployerCategory']);  // Step 5E
});

// ── Worker Routes ─────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('worker')->group(function () {
    // Badge & Status
    Route::get('/status',  [WorkerStatusController::class, 'status']);

    // Language preferences
    Route::post('/languages', [WorkerStatusController::class, 'setLanguages']);

    // Job type preferences
    Route::post('/job-types', [WorkerStatusController::class, 'setJobTypes']);

    // Document management (skippable on onboarding)
    Route::get('/documents',             [WorkerDocumentController::class, 'index']);
    Route::get('/document-checklist',    [WorkerDocumentController::class, 'checklist']);
    Route::post('/documents',            [WorkerDocumentController::class, 'store']);
    Route::post('/personal-documents',   [WorkerDocumentController::class, 'storePersonalDocument']); // Phase 1: personal identity → Verified Badge
    Route::delete('/documents/{id}',     [WorkerDocumentController::class, 'destroy']);
});

// ── Employer Staff (Agency) ───────────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('employer/staff')->group(function () {
    Route::get('/',            [EmployerStaffController::class, 'index']);
    Route::post('/invite',     [EmployerStaffController::class, 'invite']);
    Route::put('/{id}/status', [EmployerStaffController::class, 'updateStatus']);
    Route::delete('/{id}',     [EmployerStaffController::class, 'destroy']);
});

// ── Worker Directory (Employer Browse Workers) ────────────────────────────
Route::middleware('auth:sanctum')->prefix('workers')->group(function () {
    Route::get('/',     [WorkerDirectoryController::class, 'index']);
    Route::get('/{id}', [WorkerDirectoryController::class, 'show']);
});

// ── Job Listings ──────────────────────────────────────────────────────────
Route::prefix('jobs')->group(function () {
    // Public search
    Route::get('/',     [JobController::class, 'index']);
    Route::get('/{id}', [JobController::class, 'show']);

    // Authenticated job actions
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/',             [JobController::class, 'store']);
        Route::put('/{id}',          [JobController::class, 'update']);
        Route::delete('/{id}',       [JobController::class, 'destroy']);
        Route::post('/{id}/publish',   [JobController::class, 'publish']);
        Route::post('/{id}/pause',     [JobController::class, 'pause']);
        Route::post('/{id}/close',     [JobController::class, 'close']);
        Route::post('/{id}/duplicate', [JobController::class, 'duplicate']); // M4 gap

        // Applicants for a specific job (employer view)
        Route::get('/{jobId}/applicants', [JobApplicationController::class, 'jobApplicants']);

        // Apply to job (worker)
        Route::post('/{jobId}/apply', [JobApplicationController::class, 'apply']);
    });
});

// ── Employer-specific Job Listings ───────────────────────────────────────
Route::middleware(['auth:sanctum', 'is_employer'])->prefix('employer')->group(function () {
    Route::get('/jobs', [JobController::class, 'employerJobs']);
});

// ── Job Applications ──────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('applications')->group(function () {
    Route::get('/',            [JobApplicationController::class, 'myApplications']);
    Route::get('/employer',    [JobApplicationController::class, 'employerApplications']);
    Route::put('/{id}/status', [JobApplicationController::class, 'updateStatus']);
    // Worker: withdraw own application (UAT #68)
    Route::delete('/{id}',     [JobApplicationController::class, 'withdraw']);
});

// ── Chat ──────────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('chat')->group(function () {
    Route::get('/conversations',           [ChatController::class, 'conversations']);
    Route::get('/messages/{userId}',       [ChatController::class, 'messages']);
    Route::post('/messages/{userId}',      [ChatController::class, 'send']);
    Route::post('/messages/{userId}/read', [ChatController::class, 'markAsRead']);
    Route::post('/messages/{messageId}/translate', [ChatController::class, 'translate']);
});

// Custom Reverb WebSocket Auth bypass
Route::middleware('auth:sanctum')->post('/reverb/auth', function (Illuminate\Http\Request $request) {
    $socketId = $request->input('socket_id');
    $channelName = $request->input('channel_name');

    if (str_starts_with($channelName, 'private-chat.')) {
        $userId = str_replace('private-chat.', '', $channelName);
        if ((int) $request->user()->id !== (int) $userId) {
            abort(403, 'Unauthorized channel access.');
        }
    }

    $stringToSign = $socketId . ':' . $channelName;
    $secret = config('broadcasting.connections.reverb.secret');
    $signature = hash_hmac('sha256', $stringToSign, $secret);

    return response()->json([
        'auth' => config('broadcasting.connections.reverb.key') . ':' . $signature
    ]);
});

// ── Mobile Chat Compatibility Alias (/chats/* → /chat/*) ─────────────────
// Mobile calls /chats but backend is /chat
Route::middleware('auth:sanctum')->prefix('chats')->group(function () {
    Route::get('/',                 [ChatController::class, 'conversations']);
    Route::get('/{userId}',         [ChatController::class, 'messages']);
    Route::post('/{userId}',        [ChatController::class, 'send']);
    Route::put('/{messageId}/read', [ChatController::class, 'markAsRead']); // mobile uses PUT
    Route::post('/messages/{messageId}/translate', [ChatController::class, 'translate']);

    // ── New M8 features ──────────────────────────────────────────────
    Route::get('/{userId}/status',  [ChatController::class, 'chatStatus']);
    Route::post('/{userId}/close',  [ChatController::class, 'closeChat']);
    Route::post('/{userId}/reopen', [ChatController::class, 'reopenChat']);
});

// ── Block / Unblock User ──────────────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('users')->group(function () {
    Route::post('/{userId}/block',          [ChatController::class, 'blockUser']);
    Route::delete('/{userId}/block',        [ChatController::class, 'unblockUser']);
    Route::get('/{userId}/block-status',    [ChatController::class, 'blockStatus']);
});

// ── Payments & Subscriptions ─────────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('subscriptions')->group(function () {
    Route::post('/purchase',      [\App\Http\Controllers\Api\SubscriptionController::class, 'purchase']);
    Route::post('/mock-callback', [\App\Http\Controllers\Api\SubscriptionController::class, 'mockCallback']);
    Route::get('/status',         [\App\Http\Controllers\Api\SubscriptionController::class, 'status']);
    Route::get('/history',        [\App\Http\Controllers\Api\SubscriptionController::class, 'history']);
});

// ── Dashboard ─────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('dashboard')->group(function () {
    Route::get('/worker',   [\App\Http\Controllers\Api\DashboardController::class, 'worker']);
    Route::get('/employer', [\App\Http\Controllers\Api\DashboardController::class, 'employer']);
});

// ── Notifications ─────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
    Route::get('/',              [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::get('/unread-count',  [\App\Http\Controllers\Api\NotificationController::class, 'unreadCount']);
    Route::put('/read-all',      [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    Route::put('/{id}/read',     [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
    Route::post('/announcement', [\App\Http\Controllers\Api\NotificationController::class, 'announcement']);
});

// ── Reports & Trust System ────────────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('reports')->group(function () {
    Route::post('/',   [\App\Http\Controllers\Api\ReportController::class, 'store']);
    Route::get('/my',  [\App\Http\Controllers\Api\ReportController::class, 'myReports']);
});

// ── UAT #79: Admin Report Management ──────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin/reports')->group(function () {
    Route::get('/',                    [\App\Http\Controllers\Api\AdminReportController::class, 'index']);
    Route::post('/{id}/invalidate',    [\App\Http\Controllers\Api\AdminReportController::class, 'invalidate']);
    Route::post('/{id}/resolve',       [\App\Http\Controllers\Api\AdminReportController::class, 'resolve']);
});

// ── AI Safety Checker ──────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('ai')->group(function () {
    Route::post('/safety-check/job/{jobId}', [\App\Http\Controllers\Api\SafetyCheckerController::class, 'checkJob']);
    Route::post('/safety-check/messages',    [\App\Http\Controllers\Api\SafetyCheckerController::class, 'checkMessages']);
    Route::post('/safety-check/screenshot',  [\App\Http\Controllers\Api\SafetyCheckerController::class, 'checkScreenshot']);
    Route::get('/safety-check/history',      [\App\Http\Controllers\Api\SafetyCheckerController::class, 'history']);
});

// ── Admin API Routes ──────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    // Worker management (PDF Section 9)
    Route::get('/workers',                                [AdminWorkerController::class, 'index']);
    Route::get('/workers/{id}',                           [AdminWorkerController::class, 'show']);
    Route::post('/workers/{id}/approve-selfie',           [AdminWorkerController::class, 'approveSelfie']);
    Route::post('/workers/{id}/reject-selfie',            [AdminWorkerController::class, 'rejectSelfie']);
    Route::post('/workers/{id}/approve-document/{docId}', [AdminWorkerController::class, 'approveDocument']);
    Route::post('/workers/{id}/reject-document/{docId}',  [AdminWorkerController::class, 'rejectDocument']);
    Route::post('/workers/{id}/override-badge',           [AdminWorkerController::class, 'overrideBadge']);

    // Employer management (PDF Section 9: approve/reject employer permission to publish)
    Route::post('/employers/{id}/approve',                    [AdminWorkerController::class, 'approveEmployer']);
    Route::post('/employers/{id}/reject',                     [AdminWorkerController::class, 'rejectEmployer']);
    Route::post('/employers/{id}/suspend',                    [AdminWorkerController::class, 'suspendEmployer']);

    // Employer document review (per-document — grants badge when all approved)
    Route::post('/employer-documents/{docId}/approve',        [AdminWorkerController::class, 'approveEmployerDocument']);
    Route::post('/employer-documents/{docId}/reject',         [AdminWorkerController::class, 'rejectEmployerDocument']);

    // Job moderation (PDF Section 9: hide/suspend suspicious job posting)
    Route::post('/jobs/{id}/suspend',       [AdminWorkerController::class, 'suspendJob']);
    Route::post('/jobs/{id}/restore',       [AdminWorkerController::class, 'restoreJob']);

    // Job review & pending jobs from stashed API AdminJobController
    Route::get('/jobs',                     [\App\Http\Controllers\Api\AdminJobController::class, 'pendingJobs']);
    Route::put('/jobs/{id}/review',         [\App\Http\Controllers\Api\AdminJobController::class, 'reviewJob']);

    // User moderation
    Route::post('/users/{id}/suspend',      [AdminWorkerController::class, 'suspendUser']);
    Route::post('/users/{id}/restore',      [AdminWorkerController::class, 'restoreUser']);

    // ── UAT #45 & #47: Audit Logs + CSV Export ─────────────────────────────
    Route::get('/audit-logs',               [\App\Http\Controllers\Api\AdminAuditController::class, 'index']);
    Route::get('/audit-logs/export',        [\App\Http\Controllers\Api\AdminAuditController::class, 'export']);
});
