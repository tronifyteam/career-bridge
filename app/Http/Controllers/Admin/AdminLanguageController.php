<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;

class AdminLanguageController extends Controller
{
    public function index(Request $request)
    {
        $query = Language::query();
        
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(language_name) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(language_code) LIKE ?', ['%' . $search . '%']);
        }
        
        $languages = $query->orderBy('language_name')->paginate(20);
        return view('admin.languages.index', compact('languages'));
    }

    public function create()
    {
        return view('admin.languages.form', ['language' => new Language()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'language_code' => 'required|string|max:10|unique:languages',
            'language_name' => 'required|string|max:100',
        ]);

        Language::create($validated);

        return redirect()->route('admin.languages.index')->with('success', 'Language created successfully.');
    }

    public function edit(Language $language)
    {
        return view('admin.languages.form', compact('language'));
    }

    public function update(Request $request, Language $language)
    {
        $validated = $request->validate([
            'language_code' => 'required|string|max:10|unique:languages,language_code,' . $language->id,
            'language_name' => 'required|string|max:100',
        ]);

        $language->update($validated);

        return redirect()->route('admin.languages.index')->with('success', 'Language updated successfully.');
    }

    public function destroy(Language $language)
    {
        // Check if anyone is using this language first?
        // In worker_languages table:
        if ($language->workerLanguages()->exists()) {
            return redirect()->route('admin.languages.index')->with('error', 'Cannot delete language because it is currently linked to workers.');
        }

        $language->delete();
        return redirect()->route('admin.languages.index')->with('success', 'Language deleted successfully.');
    }
}
