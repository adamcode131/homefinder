<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FilterCategory;
use App\Models\FilterOption;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class FilterOptionController extends Controller
{
        public function index()
    {
        $options = FilterOption::with('filterCategory')
            ->orderBy('filter_category_id')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'options' => $options
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'filter_category_id' => 'required|exists:filter_categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Ensure slug is unique within the category
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (FilterOption::where('filter_category_id', $validated['filter_category_id'])
                          ->where('slug', $validated['slug'])
                          ->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $option = FilterOption::create($validated);
        $option->load('filterCategory');

        return response()->json([
            'option' => $option,
            'message' => 'Option créée avec succès'
        ], 201);
    }

    public function show($id)
    {
        $option = FilterOption::with('filterCategory')->findOrFail($id);

        return response()->json([
            'option' => $option
        ]);
    }

    public function update(Request $request, $id)
    {
        $option = FilterOption::findOrFail($id);

        $validated = $request->validate([
            'filter_category_id' => 'required|exists:filter_categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Ensure slug is unique within the category (excluding current option)
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (FilterOption::where('filter_category_id', $validated['filter_category_id'])
                          ->where('slug', $validated['slug'])
                          ->where('id', '!=', $id)
                          ->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $option->update($validated);
        $option->load('filterCategory');

        return response()->json([
            'option' => $option,
            'message' => 'Option mise à jour avec succès'
        ]);
    }

    public function destroy($id)
    {
        $option = FilterOption::findOrFail($id);
        
        // Check if option is being used by any products
        $productCount = $option->products()->count();
        if ($productCount > 0) {
            return response()->json([
                'message' => "Impossible de supprimer cette option. Elle est utilisée par {$productCount} produit(s)."
            ], 422);
        }

        $option->delete();

        return response()->json([
            'message' => 'Option supprimée avec succès'
        ]);
    }

    public function getByCategory($categoryId)
    {
        $category = FilterCategory::findOrFail($categoryId);
        $options = FilterOption::where('filter_category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'category' => $category,
            'options' => $options
        ]);
    }
}
