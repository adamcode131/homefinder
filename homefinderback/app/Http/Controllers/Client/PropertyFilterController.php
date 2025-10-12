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

    // public function filterProperties(Request $request)
    // {
    //     $query = Property::with(['ville', 'filterValues.filterOption.filterCategory']);
    
    //     Log::info('dd request');
    //     Log::info($request->all());

    //     // Apply filter options (existing logic)
    //     if ($request->has('filters') && !empty($request->filters)) {
    //         $filterIds = $request->filters;
    
    //         if (is_string($filterIds)) {
    //             $filterIds = json_decode($filterIds, true);
    //         }
    
    //         if (is_array($filterIds)) {
    //             $query->whereHas('filterValues', function ($q) use ($filterIds) {
    //                 $q->whereIn('filter_option_id', $filterIds);
    //             });
    //         }
    //     }
    //             // NEW: Apply ville filtering if provided
    //         if ($request->has('ville') && !empty($request->ville)) {
    //             $villeName = trim($request->ville);
    //             $query->whereHas('ville', function ($q) use ($villeName) {
    //                 $q->where('name', 'ilike', $villeName);
    //             });
    //         }

    //         // NEW: Apply quartier filtering if provided
    //         if ($request->has('quartier') && !empty($request->quartier)) {
    //             $quartierName = trim($request->quartier);
    //             $query->whereHas('quartier', function ($q) use ($quartierName) {
    //                 $q->where('name', 'ilike', $quartierName);
    //             });
    //         }

    
    //     if ($request->has('search') && !empty($request->search)) {
    //         $search = trim($request->search);
    //         $searchLower = mb_strtolower($search);
    
    //         // 1. Detect and apply PRICE filters
    //         preg_match_all('/\d+/', $search, $matches);
    //         $numbers = $matches[0] ?? [];
            
    //         $minPrice = null;
    //         $maxPrice = null;
    
    //         if (count($numbers) === 1) {
    //             $minPrice = (int)$numbers[0];
    //         } elseif (count($numbers) >= 2) {
    //             $minPrice = (int)$numbers[0];
    //             $maxPrice = (int)$numbers[1];
    //         }
    
    //         if ($minPrice !== null) {
    //             $query->where('price', '>=', $minPrice);
    //         }
    //         if ($maxPrice !== null) {
    //             $query->where('price', '<=', $maxPrice);
    //         }
    
    //         // 2. Detect and apply TYPE filter
    //         if (preg_match('/louer/i', $search)) {
    //             $query->where('type', 'rent');
    //         } elseif (preg_match('/vendre|vente/i', $search)) {
    //             $query->where('type', 'sale');
    //         }
    
    //         // 3. Detect and apply SUPERFICIE filter - UPDATED REGEX
    //         if (preg_match('/(\d+)\s*(?:m²|m2|m\s*²|metre|mètre|surface|taille|espace)/i', $search, $superficieMatch)) {
    //             $superficie = (int) $superficieMatch[1];
    
    
    //             if (str_contains($searchLower, 'moins')) {
    //                 $query->whereNotNull('superficie')
    //                     ->where('superficie', '<=', $superficie);
    //             } elseif (str_contains($searchLower, 'plus')) {
    //                 $query->whereNotNull('superficie')
    //                     ->where('superficie', '>=', $superficie);
    //             } elseif (preg_match('/entre\s+(\d+)\s*(?:m|m²)?\s+et\s+(\d+)\s*(?:m|m²)?/i', $search, $betweenMatch)) {
    //                 $min = (int) $betweenMatch[1];
    //                 $max = (int) $betweenMatch[2];
    //                 $query->whereNotNull('superficie')
    //                     ->whereBetween('superficie', [$min, $max]);
    //             } else {
    //                 // Exact or approximate match (you can add tolerance here)
    //                 $query->where('superficie', '>=', $superficie - 10)
    //                       ->where('superficie', '<=', $superficie + 10);
    //             }
    //         }
    
    //         // 4. Extract property type keywords for title search
    //         // Remove numbers, price indicators, and measurements
    //         $titleSearch = preg_replace('/\d+\s*(?:dh|mad|dirham|m|m²|metre|mètre)?/i', '', $search);
    //         $titleSearch = preg_replace('/\bentre\b|\bet\b|à\s*louer|à\s*vendre/i', '', $titleSearch);
    //         $titleSearch = trim($titleSearch);
    
    //         // Only search title if there's meaningful text left
    //         if (!empty($titleSearch) && strlen($titleSearch) > 2) {
    //             $driver = DB::getDriverName();
    //             $query->where(function ($q) use ($titleSearch, $driver) {
    //                 if ($driver === 'pgsql') {
    //                     $q->where('title', '=', $titleSearch)
    //                       ->orWhere('title', 'ILIKE', "%{$titleSearch}%");
    //                 } else {
    //                     $lowerTitleSearch = mb_strtolower($titleSearch);
    //                     $q->whereRaw('LOWER(title) = ?', [$lowerTitleSearch])
    //                       ->orWhereRaw('LOWER(title) LIKE ?', ["%{$lowerTitleSearch}%"]);
    //                 }
    //             });
    //         }
    //     }
    
    //     $Properties = $query->paginate(10);
    
    //     return response()->json($Properties);
    // }

public function filterProperties(Request $request)
{
    $query = Property::with(['ville', 'filterValues.filterOption.filterCategory']);

    Log::info('=== FILTER PROPERTIES REQUEST ===');
    Log::info('Full request:', $request->all());

    // FIX: Parse the filters parameter if it contains JSON with other parameters
    $filtersData = [];
    $filterIds = [];
    
    if ($request->has('filters') && !empty($request->filters)) {
        $filterInput = $request->filters;
        
        if (is_string($filterInput)) {
            // Try to decode as JSON - this might contain both filter IDs and other parameters
            $decodedFilters = json_decode($filterInput, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedFilters)) {
                $filtersData = $decodedFilters;
                Log::info('Parsed filters data: ', $filtersData);
                
                // Extract ONLY numeric filter IDs (not page, limit, ville, etc.)
                $filterIds = array_filter($filtersData, function($value) {
                    return is_numeric($value);
                });
                
                Log::info('Extracted filter IDs: ', $filterIds);
            } else {
                // If it's not JSON, treat it as comma-separated filter IDs (original behavior)
                $filterIds = explode(',', $filterInput);
                $filterIds = array_filter(array_map('intval', $filterIds));
            }
        } elseif (is_array($filterInput)) {
            // Original behavior for array input
            $filterIds = $filterInput;
        }
        
        // Apply filter IDs if found
        if (!empty($filterIds)) {
            $query->whereHas('filterValues', function ($q) use ($filterIds) {
                $q->whereIn('filter_option_id', $filterIds);
            });
            Log::info('Applied filter IDs: ', $filterIds);
        }
    }

    // FIX: Check both request parameters AND parsed filters data for ville
    $villeName = null;
    if ($request->has('ville') && !empty($request->ville)) {
        $villeName = trim($request->ville);
    } elseif (isset($filtersData['ville']) && !empty($filtersData['ville'])) {
        $villeName = trim($filtersData['ville']);
    }

    if ($villeName) {
        Log::info('Applying ville filter: ' . $villeName);
        $query->whereHas('ville', function ($q) use ($villeName) {
            $q->where(DB::raw('LOWER(name)'), '=', mb_strtolower($villeName));
        });
        Log::info('Ville filter applied');
    } else {
        Log::info('No ville filter to apply');
    }

    // FIX: Check both request parameters AND parsed filters data for quartier
    $quartierName = null;
    if ($request->has('quartier') && !empty($request->quartier)) {
        $quartierName = trim($request->quartier);
    } elseif (isset($filtersData['quartier']) && !empty($filtersData['quartier'])) {
        $quartierName = trim($filtersData['quartier']);
    }

    if ($quartierName) {
        Log::info('Applying quartier filter: ' . $quartierName);
        $query->whereHas('quartier', function ($q) use ($quartierName) {
            $q->where(DB::raw('LOWER(name)'), '=', mb_strtolower($quartierName));
        });
        Log::info('Quartier filter applied');
    } else {
        Log::info('No quartier filter to apply');
    }

    // FIX: Handle pagination from both sources
    $page = $request->get('page', 
        isset($filtersData['page']) ? $filtersData['page'] : 1
    );
    $limit = $request->get('limit', 
        isset($filtersData['limit']) ? $filtersData['limit'] : 50
    );

    // Debug: Check the SQL query before pagination
    Log::info('Final SQL Query: ' . $query->toSql());
    Log::info('Query Bindings: ', $query->getBindings());

    // Get count before pagination for debugging
    $count = $query->count();
    Log::info("Total properties after filtering: " . $count);

    $properties = $query->paginate($limit, ['*'], 'page', $page);

    // Log the final results for debugging
    foreach ($properties as $property) {
        Log::info('Property: ' . $property->id . ' - Ville: ' . ($property->ville->name ?? 'NULL'));
    }

    return response()->json($properties);
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
