<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmployerDocument;
use App\Models\WorkerDocument;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/auth/register
     */
    public function register(Request $request, \App\Services\WorkerStatusService $workerStatus): JsonResponse
    {
        if ($request->has('user_type')) {
            $normalized = strtolower(str_replace(' ', '_', trim($request->user_type)));
            $request->merge(['user_type' => $normalized]);
        }

        $validated = $request->validate([
            'full_name' => 'nullable|string|min:3|max:255|regex:/^[\p{L}\s\x{27}\x{2d}]+$/u',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                \Illuminate\Validation\Rules\Password::min(6)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
                'confirmed',
            ],
            'role_id' => 'nullable|string', // e.g. 'WORKER', 'EMPLOYER'
            'user_type' => 'nullable|string|in:arc_other,aprc,other,taiwanese,white_collar,blue_collar,student,company,factory,agency_company,agency,agency_staff,family_employer,family_care,gold_card,spouse_roc,not_sure',
        ]);

        $isVerified = \Illuminate\Support\Facades\Cache::pull('pre_register_verified_' . $validated['email']);

        $role = null;
        if (!empty($validated['role_id'])) {
            $roleIdRaw = strtoupper(trim($validated['role_id']));
            $roleMap = [
                'WORKER'   => 'ROLE_WORKER',
                'EMPLOYER' => 'ROLE_COMPANY',
            ];
            $roleId = $roleMap[$roleIdRaw] ?? $roleIdRaw;
            $role = \App\Models\User::roleFromRoleId($roleId);
        }

        $workerTypeSlug = null;
        $workerTypeId = null;
        $sponsorshipRequired = false;
        $onboardingStep = 1;
        $verificationStatus = 'unverified';

        if (!empty($validated['user_type'])) {
            $userTypeRaw = $validated['user_type'];

            $workerTypeMap = [
                'arc_other'    => 'arc_other',
                'aprc'         => 'aprc',
                'other'        => 'other',
                'taiwanese'    => 'taiwanese',
                'white_collar' => 'white_collar',
                'blue_collar'  => 'blue_collar',
                'student'      => 'student',
                'gold_card'    => 'gold_card',
                'spouse_roc'   => 'spouse_roc',
                'not_sure'     => 'not_sure',
            ];

            $employerRoleMap = [
                'company'         => 'company',
                'factory'         => 'factory',
                'agency_company'  => 'agency',
                'agency'          => 'agency',
                'agency_staff'    => 'agency_staff',
                'family_employer' => 'family_care',
                'family_care'     => 'family_care',
            ];

            if (isset($workerTypeMap[$userTypeRaw])) {
                $role = 'worker';
                $workerTypeSlug = $workerTypeMap[$userTypeRaw];
                $workerTypeModel = \App\Models\WorkerType::where('slug', $workerTypeSlug)->first();
                if ($workerTypeModel) {
                    $workerTypeId = $workerTypeModel->id;
                }
                if ($workerTypeSlug === 'white_collar') {
                    $sponsorshipRequired = true;
                }
                $onboardingStep = 5;
            } elseif (isset($employerRoleMap[$userTypeRaw])) {
                $role = $employerRoleMap[$userTypeRaw];
                $onboardingStep = 4;
                $verificationStatus = 'unverified';
            }
        }

        $user = User::create([
            'name' => $validated['full_name'] ?? explode('@', $validated['email'])[0],
            'full_name' => $validated['full_name'] ?? null,
            'email' => $validated['email'],
            'password' => $validated['password'],
            'email_verified_at' => $isVerified ? now() : null,
            'profile_completed' => false,
            'role' => $role,
            'worker_type' => $workerTypeSlug,
            'worker_type_id' => $workerTypeId,
            'sponsorship_required' => $sponsorshipRequired,
            'onboarding_step' => $onboardingStep,
            'verification_status' => $verificationStatus,
            'verified_badge_status' => 'unverified',
        ]);

        if ($role === 'worker' && $workerTypeSlug) {
            $workerStatus->generateDocumentChecklist($user);
            $workerStatus->evaluateReadyToWork($user);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => array_merge($user->toApiArray(), ['token' => $token]),
        ], 201);
    }

    /**
     * POST /api/auth/check-email
     * Checks if an email is already registered.
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $exists = User::where('email', $request->email)->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'is_registered' => $exists
            ],
            'message' => $exists ? 'Email is already registered' : 'Email is available'
        ]);
    }

    /**
     * POST /api/auth/send-email-otp
     * Sends OTP to the given email for pre-registration verification.
     */
    public function sendEmailOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Optional: Check again if already registered just in case
        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already registered',
            ], 400);
        }

        // Generate OTP and store in cache for 15 minutes (pre-registration)
        $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        \Illuminate\Support\Facades\Cache::put('pre_register_otp_' . $request->email, $code, now()->addMinutes(15));

        // Send Email
        \Illuminate\Support\Facades\Mail::to($request->email)->send(
            new \App\Mail\EmailVerificationMail($code, explode('@', $request->email)[0])
        );

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent successfully.'
        ]);
    }

    /**
     * POST /api/auth/verify-email-otp
     * Verifies the OTP sent by check-email (pre-registration).
     */
    public function verifyEmailOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $cachedCode = \Illuminate\Support\Facades\Cache::get('pre_register_otp_' . $request->email);

        if (!$cachedCode || $cachedCode !== $request->code) {
            return response()->json([
                'success' => false,
                'error' => 'invalid_code',
                'message' => 'Kode verifikasi tidak valid atau telah kedaluwarsa.',
            ], 400);
        }

        // Mark as verified in cache so register() can check it (valid for 60 mins)
        \Illuminate\Support\Facades\Cache::put('pre_register_verified_' . $request->email, true, now()->addMinutes(60));
        \Illuminate\Support\Facades\Cache::forget('pre_register_otp_' . $request->email);

        return response()->json([
            'success' => true,
            'message' => 'Email berhasil diverifikasi.',
        ]);
    }

    /**
     * POST /api/auth/google
     * Login or register via Google (using Firebase ID Token)
     */
    public function googleLogin(Request $request): JsonResponse
    {
        $request->validate([
            'firebase_token' => 'required|string',
        ]);

        try {
            $auth = app('firebase.auth');
            $verifiedIdToken = $auth->verifyIdToken($request->firebase_token);
            $uid = $verifiedIdToken->claims()->get('sub');
            $email = $verifiedIdToken->claims()->get('email');
            $name = $verifiedIdToken->claims()->get('name');
            $picture = $verifiedIdToken->claims()->get('picture');

            if (!$email) {
                return response()->json([
                    'success' => false,
                    'error' => 'invalid_token_payload',
                    'message' => 'Email not found in Google token.',
                ], 400);
            }

            // Find existing user by email or provider_id
            $user = User::where('email', $email)
                        ->orWhere('provider_id', $uid)
                        ->first();

            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $name ?? explode('@', $email)[0],
                    'full_name' => $name ?? null,
                    'email' => $email,
                    'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(24)),
                    'email_verified_at' => now(),
                    'profile_completed' => false,
                    'onboarding_step' => 1,
                    'verification_status' => 'unverified',
                    'verified_badge_status' => 'unverified',
                    'provider_name' => 'google',
                    'provider_id' => $uid,
                    'avatar_url' => $picture,
                ]);
            } else {
                // Update provider info if missing
                if (!$user->provider_id) {
                    $user->update([
                        'provider_name' => 'google',
                        'provider_id' => $uid,
                        'email_verified_at' => $user->email_verified_at ?? now(),
                    ]);
                }
            }

            // Revoke previous tokens
            $user->tokens()->delete();

            // ── Suspension check ──────────────────────────────────────────
            if ($user->is_suspended) {
                return response()->json([
                    'success'           => false,
                    'error'             => 'account_suspended',
                    'message'           => 'Your account has been suspended.',
                    'suspension_reason' => $user->suspension_reason ?? 'Violation of community guidelines.',
                    'suspended_at'      => $user->suspended_at?->toIso8601String(),
                ], 403);
            }

            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'data' => array_merge($user->toApiArray(), ['token' => $token]),
            ]);

        } catch (\Kreait\Firebase\Exception\Auth\FailedToVerifyToken $e) {
            return response()->json([
                'success' => false,
                'error' => 'invalid_token',
                'message' => 'The provided Google token is invalid.',
            ], 401);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Google Login Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'server_error',
                'message' => 'An error occurred during Google Sign In.',
            ], 500);
        }
    }

    /**
     * POST /api/auth/login
     *
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'error'   => 'invalid_credentials',
                'message' => 'Invalid email or password',
            ], 401);
        }

        // ── Suspension check ───────────────────────────────────────────────
        if ($user->is_suspended) {
            return response()->json([
                'success'          => false,
                'error'            => 'account_suspended',
                'message'          => 'Your account has been suspended.',
                'suspension_reason'=> $user->suspension_reason ?? 'Violation of community guidelines.',
                'suspended_at'     => $user->suspended_at?->toIso8601String(),
            ], 403);
        }

        // Revoke previous tokens
        $user->tokens()->delete();
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'data'    => array_merge($user->toApiArray(), ['token' => $token]),
        ]);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()->toApiArray(),
        ]);
    }

    /**
     * POST /api/auth/forgot-password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Generate secure random token
            $token = Str::random(60);

            // Store in password_reset_tokens table
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'email' => $user->email,
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ]
            );

            // Dynamically construct reset link matching the request host
            $host = $request->getSchemeAndHttpHost();
            $resetUrl = $host . '/password/reset/' . $token . '?email=' . urlencode($user->email);

            // Send real email via Mailable
            Mail::to($user->email)->send(
                new ResetPasswordMail($resetUrl, $user->full_name ?? $user->name)
            );
        }

        // Always return success to prevent email enumeration
        return response()->json([
            'success' => true,
            'message' => 'Reset link sent to ' . $request->email,
        ]);
    }

    /**
     * GET /password/reset/{token}
     */
    public function showResetForm(Request $request, $token)
    {
        $email = $request->email;
        return view('auth.reset-password', compact('token', 'email'));
    }

    /**
     * POST /password/reset
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                \Illuminate\Validation\Rules\Password::min(6)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
                'confirmed',
            ],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Tautan atur ulang kata sandi tidak valid atau telah kedaluwarsa.']);
        }

        // Token expiry check (60 minutes)
        if (now()->subMinutes(60)->gt($record->created_at)) {
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();
            return back()->withErrors(['email' => 'Tautan atur ulang kata sandi telah kedaluwarsa.']);
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Delete token
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return back()->with('status', 'Kata sandi Anda berhasil diperbarui! Silakan kembali ke aplikasi mobile untuk masuk.');
    }

    /**
     * PUT /api/auth/role
     * Legacy route — new flow uses POST /api/onboarding/role
     */
    public function updateRole(Request $request): JsonResponse
    {
        $request->validate([
            'role_id' => 'required|string|in:ROLE_WORKER,ROLE_COMPANY,ROLE_FACTORY,ROLE_FAMILY_CARE,ROLE_AGENCY,ROLE_AGENCY_STAFF',
        ]);

        $user = $request->user();
        $role = \App\Models\User::roleFromRoleId($request->role_id);

        $user->update([
            'role'            => $role,
            'onboarding_step' => max($user->onboarding_step ?? 1, 4),
        ]);

        return response()->json([
            'success' => true,
            'data'    => $user->fresh()->toApiArray(),
        ]);
    }

    /**
     * PUT /api/auth/profile
     */
    public function saveProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'full_name'                   => 'sometimes|required|string|min:3|max:255|regex:/^[\p{L}\s\x{27}\x{2d}]+$/u',
            'nationality'                 => 'nullable|string|max:100',
            'current_city'                => 'nullable|string|max:100',
            'company_name'                => 'nullable|string|max:255',
            'unified_business_number'     => 'nullable|string|max:50',
            'industry'                    => 'nullable|string|max:100',
            'phone'                       => 'nullable|string|max:20',
            'avatar_url'                  => 'nullable|string|max:500',
            'license_number'              => 'nullable|string|max:255',
            'license_expiry_date'         => 'nullable|date',
            'date_of_birth'               => 'nullable|date',
            'gender'                      => 'nullable|string|in:male,female,other,Male,Female',
            'address'                     => 'nullable|string|max:1000',
            'educations'                  => 'nullable|array',
            'work_experiences'            => 'nullable|array',
            'skills'                      => 'nullable|array',
            'worker_type'                 => 'nullable|string|max:100',
            'current_work_status'         => 'nullable|string|max:100',
            'language_abilities'          => 'nullable|array',
            'is_cv_public'                => 'nullable|boolean',
            'preferred_language'          => 'nullable|string|max:50',
            // New fields
            'available_date'              => 'nullable|date',
            'expected_salary'             => 'nullable|numeric|min:0',
            'sponsorship_required'        => 'nullable|boolean',
            'employer_self_check_required'=> 'nullable|boolean',
        ]);

        if (array_key_exists('full_name', $validated)) {
            $user->name      = $validated['full_name'];
            $user->full_name = $validated['full_name'];
        }
        if (array_key_exists('nationality', $validated)) {
            $user->nationality = $validated['nationality'];
        }
        if (array_key_exists('current_city', $validated)) {
            $user->current_city = $validated['current_city'];
        }
        if (array_key_exists('company_name', $validated)) {
            $user->company_name = $validated['company_name'];
        }
        if (array_key_exists('industry', $validated)) {
            $user->industry = $validated['industry'];
        }
        if (array_key_exists('phone', $validated)) {
            $user->phone = $validated['phone'];
        }
        if (array_key_exists('avatar_url', $validated)) {
            $user->avatar_url = $validated['avatar_url'];
        }
        if (array_key_exists('license_number', $validated)) {
            $user->license_number = $validated['license_number'];
        }
        if (array_key_exists('license_expiry_date', $validated)) {
            $user->license_expiry_date = $validated['license_expiry_date'];
        }
        if (array_key_exists('unified_business_number', $validated)) {
            $user->unified_business_number = $validated['unified_business_number'];
        }
        if (array_key_exists('date_of_birth', $validated)) {
            $user->date_of_birth = $validated['date_of_birth'];
        }
        if (array_key_exists('gender', $validated)) {
            $user->gender = $validated['gender'];
        }
        if (array_key_exists('address', $validated)) {
            $user->address = $validated['address'];
        }
        if (array_key_exists('educations', $validated)) {
            $user->educations = $validated['educations'] ?? [];
        }
        if (array_key_exists('work_experiences', $validated)) {
            $user->work_experiences = $validated['work_experiences'] ?? [];
        }
        if (array_key_exists('skills', $validated)) {
            $user->skills = $validated['skills'] ?? [];
        }
        if (array_key_exists('language_abilities', $validated)) {
            $user->language_abilities = $validated['language_abilities'] ?? [];
        }
        if (array_key_exists('is_cv_public', $validated)) {
            $user->is_cv_public = $validated['is_cv_public'];
        }
        if (array_key_exists('preferred_language', $validated)) {
            $user->preferred_language = $validated['preferred_language'];
        }

        // Only mark profile completed if we have name and city/nationality
        if ($user->full_name && ($user->nationality || $user->company_name)) {
            $user->profile_completed = true;
            $user->onboarding_step  = max($user->onboarding_step ?? 1, 5);
        }

        $workerTypeOrSponsorshipChanged = false;

        // New worker fields
        if (array_key_exists('available_date', $validated)) {
            $user->available_date = $validated['available_date'];
        }
        if (array_key_exists('expected_salary', $validated)) {
            $user->expected_salary = $validated['expected_salary'];
        }
        if (array_key_exists('sponsorship_required', $validated)) {
            $newSponsorshipValue = (bool) $validated['sponsorship_required'];
            // Guard: worker cannot self-remove sponsorship requirement without admin-approved open work permit
            if ($newSponsorshipValue === false && $user->isWorker()) {
                if ($user->open_work_right_status !== 'approved') {
                    return response()->json([
                        'success' => false,
                        'error'   => 'open_work_right_not_approved',
                        'message' => 'Upload and receive admin approval for your Open Work Permit (APRC / Gold Card) before removing the sponsorship requirement.',
                    ], 403);
                }
            }
            $user->sponsorship_required = $newSponsorshipValue;
            $workerTypeOrSponsorshipChanged = true;
        }
        if (array_key_exists('employer_self_check_required', $validated)) {
            $user->employer_self_check_required = $validated['employer_self_check_required'];
        }

        // Sync worker_type → worker_type_id if changed
        if (array_key_exists('worker_type', $validated)) {
            if ($user->worker_type && $user->worker_type !== $validated['worker_type']) {
                return response()->json([
                    'success' => false,
                    'error'   => 'cannot_change_worker_type',
                    'message' => 'Worker type cannot be changed once set.',
                ], 422);
            }
            $user->worker_type = $validated['worker_type'];
            $workerTypeOrSponsorshipChanged = true;
            if ($validated['worker_type']) {
                $workerTypeModel = \App\Models\WorkerType::where('slug', $validated['worker_type'])->first();
                if ($workerTypeModel) {
                    $user->worker_type_id = $workerTypeModel->id;
                    // White collar → set sponsorship_required by default
                    if ($validated['worker_type'] === 'white_collar' && ! isset($validated['sponsorship_required'])) {
                        $user->sponsorship_required = true;
                    }
                }
            } else {
                $user->worker_type_id = null;
            }
        }

        if (array_key_exists('current_work_status', $validated)) {
            $user->current_work_status = $validated['current_work_status'];
        }


        $user->save();

        if ($workerTypeOrSponsorshipChanged && $user->isWorker()) {
            resolve(\App\Services\WorkerStatusService::class)->evaluateReadyToWork($user->fresh());
        }

        return response()->json([
            'success' => true,
            'data'    => $user->fresh()->toApiArray(),
        ]);
    }

    /**
     * POST /api/auth/avatar
     * Upload user avatar.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // Up to 5MB
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('avatars', $filename, 'public');

            // Delete old avatar if exists and not default
            if ($user->avatar_url) {
                $oldPath = str_replace(url('/storage'), 'public', $user->avatar_url);
                \Illuminate\Support\Facades\Storage::delete($oldPath);
            }

            // Generate full public URL
            $url = asset('storage/' . $path);

            $user->update(['avatar_url' => $url]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar uploaded successfully.',
                'data' => $user->fresh()->toApiArray(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No file uploaded.',
        ], 400);
    }

    /**
     * DELETE /api/auth/avatar
     * Delete user avatar.
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->avatar_url) {
            $oldPath = str_replace(url('/storage'), 'public', $user->avatar_url);
            \Illuminate\Support\Facades\Storage::delete($oldPath);
            
            $user->update(['avatar_url' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar deleted successfully.',
                'data' => $user->fresh()->toApiArray(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No avatar found to delete.',
        ], 404);
    }

    /**
     * POST /api/auth/cv
     * Upload worker CV (PDF).
     */
    public function uploadCv(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'cv' => 'required|file|mimes:pdf|max:15360', // PDF up to 15MB (UAT #44)
        ]);

        if ($request->hasFile('cv')) {
            $file = $request->file('cv');
            $filename = 'cv_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('cvs', $filename, 'public');

            // Generate full public URL
            $url = asset('storage/' . $path);

            $user->update(['cv_url' => $url]);

            $docType = \App\Models\DocumentType::where('slug', 'cv')->first();
            if ($docType) {
                $document = \App\Models\WorkerDocument::updateOrCreate(
                    ['user_id' => $user->id, 'document_type_id' => $docType->id],
                    [
                        'file_url'          => $url,
                        'original_filename' => $file->getClientOriginalName(),
                        'review_status'     => $docType->verification_required ? 'pending' : 'approved',
                    ]
                );

                \App\Models\WorkerDocumentRequirement::updateOrCreate(
                    ['user_id' => $user->id, 'document_type_id' => $docType->id],
                    [
                        'upload_status'      => $docType->verification_required ? 'uploaded' : 'verified',
                        'worker_document_id' => $document->id,
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'CV uploaded successfully.',
                'data' => $user->fresh()->toApiArray(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No file uploaded.',
        ], 400);
    }

    /**
     * DELETE /api/auth/cv
     * Delete worker CV.
     */
    public function deleteCv(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->cv_url) {
            // Extact file path from URL (assuming /storage/cvs/filename.pdf)
            $path = str_replace(url('/storage'), 'public', $user->cv_url);
            \Illuminate\Support\Facades\Storage::delete($path);
            
            $user->update(['cv_url' => null]);

            $docType = \App\Models\DocumentType::where('slug', 'cv')->first();
            if ($docType) {
                \App\Models\WorkerDocument::where('user_id', $user->id)
                    ->where('document_type_id', $docType->id)
                    ->delete();
                \App\Models\WorkerDocumentRequirement::where('user_id', $user->id)
                    ->where('document_type_id', $docType->id)
                    ->update(['upload_status' => 'not_uploaded', 'worker_document_id' => null]);
            }

            return response()->json([
                'success' => true,
                'message' => 'CV deleted successfully.',
                'data' => $user->fresh()->toApiArray(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No CV found to delete.',
        ], 404);
    }

    /**
     * POST /api/auth/employer/document
     * Upload employer verification document.
     */
    public function uploadDocument(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isWorker()) {
            return response()->json([
                'success' => false,
                'error' => 'unauthorized',
                'message' => 'Hanya pemberi kerja yang dapat mengunggah dokumen verifikasi.',
            ], 403);
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,png,jpeg|max:102400',
            'document_type' => 'required|string|in:company_registration,factory_permit,agency_license,family_employer_id,agency_staff_card,factory_registration,contact_person_authorization,care_recipient_id,basic_care_need_proof,relationship_proof,private_employment_service_agency_permit,employer_authorization,job_source_proof,fee_table',
        ]);
        // PDF Section 7:
        // company_registration → Company / Factory
        // factory_permit       → Factory
        // agency_license       → Agency Company
        // family_employer_id   → Family Employer (看護工)
        // agency_staff_card    → Agency Staff (業務 business card / approval letter)

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $filename = 'doc_' . $request->document_type . '_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('employer_documents', $filename, 'public');

            $url = asset('storage/' . $path);

            $document = EmployerDocument::create([
                'user_id' => $user->id,
                'document_type' => $request->document_type,
                'document_url' => $url,
                'status' => 'pending',
            ]);

            // Only update verification status if it's an employer verification document
            $jobDocTypes = ['employer_authorization', 'job_source_proof', 'fee_table'];
            if (!in_array($request->document_type, $jobDocTypes)) {
                $user->update([
                    'verification_status' => 'pending',
                    'verified_badge_status' => 'pending',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diunggah dan sedang ditinjau.',
                'data' => $document->toApiArray(),
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'No file uploaded.',
        ], 400);
    }

    /**
     * POST /api/auth/worker/document
     * Upload worker verification document.
     */
    public function uploadWorkerDocument(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isWorker()) {
            return response()->json([
                'success' => false,
                'error' => 'unauthorized',
                'message' => 'Hanya pekerja yang dapat mengunggah dokumen verifikasi pekerja.',
            ], 403);
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,png,jpeg|max:102400',
            'document_type' => 'required|string|in:selfie,student_work_permit,transfer_document,contract_ending_proof,personal_document,open_work_permit',
        ]);

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $filename = 'wdoc_' . $request->document_type . '_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('worker_documents', $filename, 'public');

            $url = asset('storage/' . $path);

            // Resolve document_type_id from slug
            $docType = \App\Models\DocumentType::where('slug', $request->document_type)->first();

            $document = WorkerDocument::create([
                'user_id'           => $user->id,
                'document_type_id'  => $docType?->id,
                'file_url'          => $url,
                'original_filename' => $file->getClientOriginalName(),
                'review_status'     => 'pending',
            ]);

            // Update user status based on document type
            if (in_array($request->document_type, ['selfie', 'personal_document'])) {
                $user->update(['verified_badge_status' => 'pending']);
            } elseif ($request->document_type === 'open_work_permit') {
                // Open work right claim — pending admin review
                $user->update(['open_work_right_status' => 'pending']);
            } else {
                $user->update(['ready_to_work_status' => 'pending']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Dokumen pekerja berhasil diunggah dan sedang ditinjau.',
                'data' => $document->toApiArray(),
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'No file uploaded.',
        ], 400);
    }

    /**
     * POST /api/auth/fcm-token
     * Update FCM Token for the authenticated user.
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        // Hapus token ini dari pengguna lain untuk mencegah kebocoran notifikasi saat berganti akun di satu device
        \App\Models\User::where('fcm_token', $request->fcm_token)->update(['fcm_token' => null]);

        $user = $request->user();
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'FCM Token updated successfully.',
        ]);
    }

    public function updateNotificationPreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'preferences' => 'required|array',
            'preferences.job_alerts' => 'boolean',
            'preferences.chat_messages' => 'boolean',
            'preferences.system_updates' => 'boolean',
            'preferences.promotions' => 'boolean',
        ]);

        $user = $request->user();
        
        $currentPrefs = $user->notification_preferences ?? [
            'job_alerts' => true,
            'chat_messages' => true,
            'system_updates' => true,
            'promotions' => true,
        ];

        // Merge new preferences
        $newPrefs = array_merge($currentPrefs, $validated['preferences']);
        
        $user->notification_preferences = $newPrefs;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated successfully.',
            'data' => [
                'notification_preferences' => $user->notification_preferences
            ]
        ]);
    }
}
