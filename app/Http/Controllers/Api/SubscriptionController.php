<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * POST /api/subscriptions/purchase
     * Initiate a subscription purchase.
     */
    public function purchase(Request $request): JsonResponse
    {
        $request->validate([
            'plan_type' => 'required|string|in:daily,weekly,monthly',
            'payment_gateway' => 'required|string|in:line_pay,jkopay,stripe,mock',
        ]);

        $prices = [
            'daily' => 29.00,    // NTD
            'weekly' => 149.00,  // NTD
            'monthly' => 499.00, // NTD
        ];

        $amount = $prices[$request->plan_type];

        $payment = Payment::create([
            'user_id' => $request->user()->id,
            'amount' => $amount,
            'payment_gateway' => $request->payment_gateway,
            'transaction_id' => 'TXN_' . strtoupper(uniqid()),
            'status' => 'pending',
        ]);

        // Mock checkout URL depending on gateway
        $checkoutUrl = "https://checkout.mock-gateway.com/pay/" . $payment->transaction_id;

        return response()->json([
            'success' => true,
            'message' => 'Silakan lakukan pembayaran melalui URL checkout.',
            'data' => [
                'payment' => $payment->toApiArray(),
                'checkout_url' => $checkoutUrl,
            ],
        ], 201);
    }

    /**
     * POST /api/subscriptions/mock-callback
     * Simulates payment gateway success/fail callback to provision quota.
     */
    public function mockCallback(Request $request): JsonResponse
    {
        $request->validate([
            'payment_id' => 'required|integer|exists:payments,id',
            'status' => 'required|string|in:completed,failed',
        ]);

        $payment = Payment::find($request->payment_id);

        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'error' => 'already_processed',
                'message' => 'Pembayaran ini sudah diproses sebelumnya.',
            ], 400);
        }

        $payment->update(['status' => $request->status]);

        if ($request->status === 'completed') {
            // Provision subscription
            $days = [
                'daily' => 1,
                'weekly' => 7,
                'monthly' => 30,
            ];

            $quotas = [
                'daily' => 100,
                'weekly' => 1000,
                'monthly' => 5000,
            ];

            // Get payment's plan type (we can mock or derive it from amount)
            $planType = 'daily';
            if ($payment->amount == 149.00) {
                $planType = 'weekly';
            } elseif ($payment->amount == 499.00) {
                $planType = 'monthly';
            }

            // Create subscription record
            $startsAt = now();
            $expiresAt = now()->addDays($days[$planType]);

            $subscription = Subscription::create([
                'user_id' => $payment->user_id,
                'plan_type' => $planType,
                'chat_translation_quota' => $quotas[$planType],
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
                'status' => 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diselesaikan, paket telah diaktifkan.',
                'data' => [
                    'payment' => $payment->toApiArray(),
                    'subscription' => $subscription->toApiArray(),
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran gagal.',
            'data' => [
                'payment' => $payment->toApiArray(),
            ],
        ]);
    }

    /**
     * GET /api/subscriptions/status
     * Get active subscription details and translation quota.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $activeSub = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if (!$activeSub) {
            $cacheKey = 'free_translate_' . $user->id . '_' . now()->toDateString();
            $used = \Illuminate\Support\Facades\Cache::get($cacheKey, 0);
            $remaining = max(0, 5 - $used);

            return response()->json([
                'success' => true,
                'data' => [
                    'plan_type' => 'free',
                    'chat_translation_quota' => $remaining,
                ],
                'message' => 'Anda sedang menggunakan paket gratis harian.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $activeSub->toApiArray(),
        ]);
    }

    /**
     * GET /api/subscriptions/history
     * Get payment history for the user.
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        $payments = Payment::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payments->map(fn($p) => $p->toApiArray()),
        ]);
    }
}
