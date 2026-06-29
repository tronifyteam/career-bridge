<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\Industry;
use App\Models\Nationality;
use Illuminate\Support\Facades\Cache;

class MasterDataController extends Controller
{
    public function nationalities()
    {
        $nationalities = Cache::remember('meta.nationalities', 86400, fn() => Nationality::orderBy('name')->get(['id', 'name', 'code']));
        return response()->json([
            'success' => true,
            'data' => $nationalities
        ]);
    }

    public function skills()
    {
        $skills = Cache::remember('meta.skills', 86400, fn() => Skill::orderBy('name')->get(['id', 'name', 'slug']));
        return response()->json([
            'success' => true,
            'data' => $skills
        ]);
    }

    public function industries()
    {
        $industries = Cache::remember('meta.industries', 86400, fn() => Industry::orderBy('name')->get(['id', 'name', 'slug']));
        return response()->json([
            'success' => true,
            'data' => $industries
        ]);
    }
}
