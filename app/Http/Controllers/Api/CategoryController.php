<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * GET /api/categories
     */
    public function categories(): JsonResponse
    {
        $categories = Cache::remember('meta.categories', 86400, fn() => Category::orderBy('name')->get());

        return response()->json([
            'success' => true,
            'data' => $categories->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'icon' => $c->icon,
            ])->values(),
        ]);
    }

    /**
     * GET /api/cities
     */
    public function cities(): JsonResponse
    {
        $cities = Cache::remember('meta.cities', 86400, fn() => City::orderBy('name')->get());

        return response()->json([
            'success' => true,
            'data' => $cities->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'region' => $c->region,
            ])->values(),
        ]);
    }
}
