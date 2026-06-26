<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdvertisementController extends Controller
{
    /**
     * Get active banner ads.
     */
    public function getBanners()
    {
        try {
            $banners = Advertisement::where('type', 'banner')
                ->where('status', 'active')
                ->where(function($q) {
                    $q->whereNull('starts_at')
                      ->orWhere('starts_at', '<=', now());
                })
                ->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->inRandomOrder()
                ->limit(5)
                ->get();

            // Record impressions
            foreach ($banners as $banner) {
                $banner->increment('impressions_count');
            }

            $mapped = $banners->map(function ($banner) {
                // Determine if image_url is relative or absolute
                $imageUrl = $banner->image_url;
                if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
                    $imageUrl = url('storage/' . $imageUrl);
                }

                return [
                    'id' => (string) $banner->id,
                    'title' => $banner->title,
                    'image_url' => $imageUrl,
                    'target_url' => $banner->target_url,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $mapped
            ]);
        } catch (\Exception $e) {
            Log::error('Get Banners Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch banners'
            ], 500);
        }
    }

    /**
     * Track a click on an advertisement.
     */
    public function trackClick($id)
    {
        try {
            $ad = Advertisement::findOrFail($id);
            if ($ad->status !== 'active') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ad is not active'
                ], 400);
            }
            $ad->increment('clicks_count');

            return response()->json([
                'status' => 'success',
                'message' => 'Click tracked'
            ]);
        } catch (\Exception $e) {
            Log::error('Track Click Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to track click'
            ], 500);
        }
    }
}
