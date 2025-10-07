<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\FilterCategory;
use App\Models\FilterOption;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyFilterController extends Controller
{
    public function getFilters()
    {
        $filters = FilterCategory::
            // ::forEntityType('App\Models\Property')
            with('activeOptions')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        Log::info($filters);

        return response()->json([
            'filters' => $filters
        ]);
    }

    public function filterProperties(Request $request)
    {
        $query = Property::with(['ville', 'filterValues.filterOption.filterCategory']);
    
        Log::info('dd request');
        Log::info($request->all());

        // Apply filter options (existing logic)
        if ($request->has('filters') && !empty($request->filters)) {
            $filterIds = $request->filters;
    
            if (is_string($filterIds)) {
                $filterIds = json_decode($filterIds, true);
            }
    
            if (is_array($filterIds)) {
                $query->whereHas('filterValues', function ($q) use ($filterIds) {
                    $q->whereIn('filter_option_id', $filterIds);
                });
            }
        }
    
        if ($request->has('search') && !empty($request->search)) {
            $search = trim($request->search);
            $searchLower = mb_strtolower($search);
    
            // 1. Detect and apply PRICE filters
            preg_match_all('/\d+/', $search, $matches);
            $numbers = $matches[0] ?? [];
            
            $minPrice = null;
            $maxPrice = null;
    
            if (count($numbers) === 1) {
                $minPrice = (int)$numbers[0];
            } elseif (count($numbers) >= 2) {
                $minPrice = (int)$numbers[0];
                $maxPrice = (int)$numbers[1];
            }
    
            if ($minPrice !== null) {
                $query->where('price', '>=', $minPrice);
            }
            if ($maxPrice !== null) {
                $query->where('price', '<=', $maxPrice);
            }
    
            // 2. Detect and apply TYPE filter
            if (preg_match('/louer/i', $search)) {
                $query->where('type', 'rent');
            } elseif (preg_match('/vendre|vente/i', $search)) {
                $query->where('type', 'sale');
            }
    
            // 3. Detect and apply SUPERFICIE filter - UPDATED REGEX
            if (preg_match('/(\d+)\s*(?:m²|m2|m\s*²|metre|mètre|surface|taille|espace)/i', $search, $superficieMatch)) {
                $superficie = (int) $superficieMatch[1];
    
    
                if (str_contains($searchLower, 'moins')) {
                    $query->whereNotNull('superficie')
                        ->where('superficie', '<=', $superficie);
                } elseif (str_contains($searchLower, 'plus')) {
                    $query->whereNotNull('superficie')
                        ->where('superficie', '>=', $superficie);
                } elseif (preg_match('/entre\s+(\d+)\s*(?:m|m²)?\s+et\s+(\d+)\s*(?:m|m²)?/i', $search, $betweenMatch)) {
                    $min = (int) $betweenMatch[1];
                    $max = (int) $betweenMatch[2];
                    $query->whereNotNull('superficie')
                        ->whereBetween('superficie', [$min, $max]);
                } else {
                    // Exact or approximate match (you can add tolerance here)
                    $query->where('superficie', '>=', $superficie - 10)
                          ->where('superficie', '<=', $superficie + 10);
                }
            }
    
            // 4. Extract property type keywords for title search
            // Remove numbers, price indicators, and measurements
            $titleSearch = preg_replace('/\d+\s*(?:dh|mad|dirham|m|m²|metre|mètre)?/i', '', $search);
            $titleSearch = preg_replace('/\bentre\b|\bet\b|à\s*louer|à\s*vendre/i', '', $titleSearch);
            $titleSearch = trim($titleSearch);
    
            // Only search title if there's meaningful text left
            if (!empty($titleSearch) && strlen($titleSearch) > 2) {
                $driver = DB::getDriverName();
                $query->where(function ($q) use ($titleSearch, $driver) {
                    if ($driver === 'pgsql') {
                        $q->where('title', '=', $titleSearch)
                          ->orWhere('title', 'ILIKE', "%{$titleSearch}%");
                    } else {
                        $lowerTitleSearch = mb_strtolower($titleSearch);
                        $q->whereRaw('LOWER(title) = ?', [$lowerTitleSearch])
                          ->orWhereRaw('LOWER(title) LIKE ?', ["%{$lowerTitleSearch}%"]);
                    }
                });
            }
        }
    
        $Properties = $query->paginate(20);
    
        return response()->json($Properties);
    }
    
    

    public function getFilterStats(Request $request)
    {
        $selectedFilters = $request->get('filters', []);
        if (is_string($selectedFilters)) {
            $selectedFilters = explode(',', $selectedFilters);
        }
        $selectedFilters = array_filter(array_map('intval', $selectedFilters));

        $selectedFiltersByCategory = [];
        if (!empty($selectedFilters)) {
            $filterOptions = FilterOption::with('filterCategory')->whereIn('id', $selectedFilters)->get();
            foreach ($filterOptions as $filterOption) {
                $selectedFiltersByCategory[$filterOption->filterCategory->id][] = $filterOption->id;
            }
        }

        $categories = FilterCategory::forEntityType('Property')
            ->with('activeOptions')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        foreach ($categories as $category) {
            foreach ($category->activeOptions as $option) {
                $query = Property::query();

                foreach ($selectedFiltersByCategory as $catId => $filterIds) {
                    if ($catId != $category->id) {
                        $query->whereHas('filterValues', function ($q) use ($filterIds) {
                            $q->whereIn('filter_option_id', $filterIds);
                        });
                    }
                }

                $query->whereHas('filterValues', function ($q) use ($option) {
                    $q->where('filter_option_id', $option->id);
                });

                if ($request->has('search') && !empty($request->search)) {
                    $search = $request->search;
                    $driver = DB::getDriverName();
                    $query->where(function ($q) use ($search, $driver) {
                        if ($driver === 'pgsql') {
                            $q->where('name', 'ILIKE', "%{$search}%");
                        } else {
                            $lower = mb_strtolower($search);
                            $q->whereRaw('LOWER(name) LIKE ?', ["%{$lower}%"]);
                        }
                    });
                }

                $option->Property_count = $query->count();
            }
        }

        return response()->json([
            'filter_stats' => $categories
        ]);
    }
}
