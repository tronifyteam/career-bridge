<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\Industry;
use App\Models\Nationality;

class MasterDataController extends Controller
{
    public function nationalities()
    {
        $nationalities = Nationality::orderBy('name')->get(['id', 'name', 'code']);
        return response()->json([
            'success' => true,
            'data' => $nationalities
        ]);
    }

    public function skills()
    {
        $skills = Skill::orderBy('name')->get(['id', 'name', 'slug']);
        return response()->json([
            'success' => true,
            'data' => $skills
        ]);
    }

    public function industries()
    {
        $industries = Industry::orderBy('name')->get(['id', 'name', 'slug']);
        return response()->json([
            'success' => true,
            'data' => $industries
        ]);
    }
}
