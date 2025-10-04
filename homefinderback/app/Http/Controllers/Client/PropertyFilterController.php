<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\FilterCategory;
use App\Models\FilterOption;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator; // ✅ Added
use Illuminate\Pagination\Paginator;            // ✅ Added

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
    
            // Detect numeric price ranges (e.g. 1000 à 2000)
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
    
            // Detect type (à louer / à vendre)
            $type = null;
            if (preg_match('/louer/i', $search)) {
                $type = 'rent';
            } elseif (preg_match('/vendre|vente/i', $search)) {
                $type = 'sale';
            }
    
            // Apply title search
            $driver = DB::getDriverName();
            $query->where(function ($q) use ($search, $driver) {
                if ($driver === 'pgsql') {
                    $q->where('title', '=', $search)
                      ->orWhere('title', 'ILIKE', "%{$search}%");
                } else {
                    $lowerSearch = mb_strtolower($search);
                    $q->whereRaw('LOWER(title) = ?', [$lowerSearch])
                      ->orWhereRaw('LOWER(title) LIKE ?', ["%{$lowerSearch}%"]);
                }
            });
    
            // Apply price filters if found
            if ($minPrice !== null) {
                $query->where('price', '>=', $minPrice);
            }
            if ($maxPrice !== null) {
                $query->where('price', '<=', $maxPrice);
            }
    
            // Apply type filter if found
            if ($type) {
                $query->where('type', $type); // Make sure your DB has a 'type' column ('rent' or 'sale')
            }
        }
    
        $Properties = $query->get();
    
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
