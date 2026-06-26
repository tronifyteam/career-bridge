<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nationality;
use Illuminate\Http\Request;

class AdminNationalityController extends Controller
{
    public function index(Request $request)
    {
        $query = Nationality::query();
        
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(code) LIKE ?', ['%' . $search . '%']);
        }
        
        $nationalities = $query->orderBy('name')->paginate(20);
        return view('admin.nationalities.index', compact('nationalities'));
    }

    public function create()
    {
        return view('admin.nationalities.form', ['nationality' => new Nationality()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:nationalities',
            'code' => 'nullable|string|max:10|unique:nationalities',
        ]);

        Nationality::create($validated);

        return redirect()->route('admin.nationalities.index')->with('success', 'Nationality created successfully.');
    }

    public function edit(Nationality $nationality)
    {
        return view('admin.nationalities.form', compact('nationality'));
    }

    public function update(Request $request, Nationality $nationality)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:nationalities,name,' . $nationality->id,
            'code' => 'nullable|string|max:10|unique:nationalities,code,' . $nationality->id,
        ]);

        $nationality->update($validated);

        return redirect()->route('admin.nationalities.index')->with('success', 'Nationality updated successfully.');
    }

    public function destroy(Nationality $nationality)
    {
        // Check if any user is using this nationality string
        // Users might be using the string representation in nationality field
        $isUsed = \App\Models\User::where('nationality', $nationality->name)->exists();
        if ($isUsed) {
            return redirect()->route('admin.nationalities.index')->with('error', 'Cannot delete nationality because it is currently chosen by users.');
        }

        $nationality->delete();
        return redirect()->route('admin.nationalities.index')->with('success', 'Nationality deleted successfully.');
    }
}
