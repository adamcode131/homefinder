<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FilterCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class FilterCategoryController extends Controller
{
    public function index()
    {
        $categories = FilterCategory::with('activeOptions')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'categories' => $categories
        ]);
    }

    /**
     * Get filter categories for properties only
     */
    public function forProperties()
    {
        $categories = FilterCategory::forEntityType('property')
            ->with('activeOptions')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'categories' => $categories
        ]);
    }

    /**
     * Get filter categories for systems only
     */
    public function forSystems()
    {
        $categories = FilterCategory::forEntityType('system')
            ->with('activeOptions')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:filter_categories,slug',
            'entity_types' => 'nullable|array',
            'entity_types.*' => 'in:property,system',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        // Valeur par défaut pour entity_types
        if (empty($validated['entity_types'])) {
            $validated['entity_types'] = ['property'];
        }

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Ensure slug is unique
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (FilterCategory::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $category = FilterCategory::create($validated);
        return response()->json($category->load('activeOptions'), 201);
    }

    public function show($id)
    {
        $category = FilterCategory::with('activeOptions')->findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = FilterCategory::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:filter_categories,slug,' . $id,
            'entity_types' => 'nullable|array',
            'entity_types.*' => 'in:property,system',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        // Valeur par défaut pour entity_types
        if (empty($validated['entity_types'])) {
            $validated['entity_types'] = ['property'];
        }

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Ensure slug is unique (excluding current record)
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (FilterCategory::where('slug', $validated['slug'])->where('id', '!=', $id)->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $category->update($validated);
        return response()->json($category->load('activeOptions'));
    }

    public function destroy($id)
    {
        $category = FilterCategory::findOrFail($id);
        
        // Check if category has options
        if ($category->options()->count() > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer cette catégorie car elle contient des options. Supprimez d\'abord toutes les options.'
            ], 422);
        }

        $category->delete();
        return response()->json(['message' => 'Catégorie supprimée avec succès']);
    }
}
