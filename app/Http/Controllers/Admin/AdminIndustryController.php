<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminIndustryController extends Controller
{
    public function index(Request $request)
    {
        $query = Industry::query();
        
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%']);
        }
        
        $industries = $query->orderBy('name')->paginate(20);
        return view('admin.industries.index', compact('industries'));
    }

    public function create()
    {
        return view('admin.industries.form', ['industry' => new Industry()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:industries',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Industry::create($validated);

        return redirect()->route('admin.industries.index')->with('success', 'Industry created successfully.');
    }

    public function edit(Industry $industry)
    {
        return view('admin.industries.form', compact('industry'));
    }

    public function update(Request $request, Industry $industry)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:industries,name,' . $industry->id,
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $industry->update($validated);

        return redirect()->route('admin.industries.index')->with('success', 'Industry updated successfully.');
    }

    public function destroy(Industry $industry)
    {
        $industry->delete();
        return redirect()->route('admin.industries.index')->with('success', 'Industry deleted successfully.');
    }
}
