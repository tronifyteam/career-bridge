<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VerificationCode;
use App\Mail\EmailVerificationMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class VerificationController extends Controller
{
    /**
     * Send email verification code.
     */
    public function sendEmailCode(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'error' => 'already_verified',
                'message' => 'Alamat email Anda sudah terverifikasi sebelumnya.',
            ], 400);
        }

        // Expiry time: 15 minutes from now
        $expiresAt = now()->addMinutes(15);
        $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Delete any existing codes of same type to prevent clutter
        VerificationCode::where('user_id', $user->id)
            ->where('type', 'email')
            ->delete();

        // Create code record
        VerificationCode::create([
            'user_id' => $user->id,
            'type' => 'email',
            'target' => $user->email,
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);

        // Send Email
        Mail::to($user->email)->send(
            new EmailVerificationMail($code, $user->full_name ?? $user->name)
        );

        return response()->json([
            'success' => true,
            'message' => 'Kode verifikasi email berhasil dikirim ke ' . $user->email,
        ]);
    }

    /**
     * Verify email with 6-digit OTP code.
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'error' => 'already_verified',
                'message' => 'Alamat email Anda sudah terverifikasi sebelumnya.',
            ], 400);
        }

        $verification = VerificationCode::where('user_id', $user->id)
            ->where('type', 'email')
            ->where('code', $request->code)
            ->active()
            ->first();

        if (!$verification) {
            return response()->json([
                'success' => false,
                'error' => 'invalid_code',
                'message' => 'Kode verifikasi tidak valid atau telah kedaluwarsa.',
            ], 400);
        }

        // Mark user email verified
        $user->email_verified_at = now();
        $user->save();

        // Delete verification code
        $verification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Email Anda berhasil diverifikasi.',
            'data' => $user->fresh()->toApiArray(),
        ]);
    }

    /**
     * Send phone OTP code (optional).
     */
    public function sendPhoneOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|min:8|max:20',
        ]);

        $user = $request->user();
        $phone = $request->phone;

        // Expiry time: 15 minutes
        $expiresAt = now()->addMinutes(15);
        $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Delete existing phone OTPs for user
        VerificationCode::where('user_id', $user->id)
            ->where('type', 'phone')
            ->delete();

        // Create verification code
        VerificationCode::create([
            'user_id' => $user->id,
            'type' => 'phone',
            'target' => $phone,
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);

        // Log SMS OTP (representing gateway API dispatch)
        Log::info(sprintf("[SMS OTP GATEWAY] Sending to %s, Message: [2ne5] Kode verifikasi Anda adalah %s. Rahasiakan kode ini.", $phone, $code));

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP berhasil dikirim ke nomor ' . $phone,
        ]);
    }

    /**
     * Verify phone OTP.
     */
    public function verifyPhoneOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();
        $phone = $request->phone;

        $verification = VerificationCode::where('user_id', $user->id)
            ->where('type', 'phone')
            ->where('target', $phone)
            ->where('code', $request->code)
            ->active()
            ->first();

        if (!$verification) {
            return response()->json([
                'success' => false,
                'error' => 'invalid_code',
                'message' => 'Kode verifikasi OTP tidak valid atau telah kedaluwarsa.',
            ], 400);
        }

        // Save phone and verified timestamp
        $user->update([
            'phone' => $phone,
            'phone_verified_at' => now(),
        ]);

        // Clean up code
        $verification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nomor HP Anda berhasil diverifikasi.',
            'data' => $user->fresh()->toApiArray(),
        ]);
    }

    /**
     * Verify Firebase Phone Auth token.
     */
    public function verifyFirebasePhone(Request $request): JsonResponse
    {
        $request->validate([
            'firebase_token' => 'required|string',
        ]);

        $user = $request->user();

        try {
            $auth = app('firebase.auth');
            $verifiedIdToken = $auth->verifyIdToken($request->firebase_token);
            $uid = $verifiedIdToken->claims()->get('sub');
            
            $firebaseUser = $auth->getUser($uid);
            $phone = $firebaseUser->phoneNumber;

            if (!$phone) {
                return response()->json([
                    'success' => false,
                    'error' => 'invalid_firebase_user',
                    'message' => 'Nomor HP tidak ditemukan di token Firebase.',
                ], 400);
            }

            // Save phone and verified timestamp
            $user->update([
                'phone' => $phone,
                'phone_verified_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nomor HP Anda berhasil diverifikasi.',
                'data' => $user->fresh()->toApiArray(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'invalid_token',
                'message' => 'Token verifikasi Firebase tidak valid atau kedaluwarsa: ' . $e->getMessage(),
            ], 400);
        }
    }
}
