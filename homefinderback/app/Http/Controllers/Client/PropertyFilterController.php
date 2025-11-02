<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\FilterCategory;
use App\Models\FilterOption;
use App\Models\Property;
use App\Models\Quartier;
use App\Models\Ville;
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







    // filterOption + location  = working
    // filterOption  = working 
    // filterOption + radius  = working
    public function filterProperties(Request $request)
    {
        try {
            Log::info('=== FILTER PROPERTIES REQUEST ===');
            Log::info('Full request:', $request->all());

            $query = Property::with(['ville', 'filterValues.filterOption.filterCategory'])
                ->select('properties.*');

            // --- Parse filters (array of filter_option ids) ---
            $filterIds = [];
            if ($request->has('filters')) {
                $filtersData = $request->filters;
                if (is_string($filtersData)) {
                    $filtersData = json_decode($filtersData, true);
                }
                if (is_array($filtersData)) {
                    $filterIds = array_map('intval', $filtersData);
                }
            }

            if (!empty($filterIds)) {
                // require property to have any of the provided filter_option ids
                // Use the full class name to match the model's relationship
                $query->whereHas('filterValues', function ($q) use ($filterIds) {
                    $q->whereIn('filter_option_id', $filterIds);
                });
            }

            Log::info('filtersData : ', is_array($filtersData) ? $filtersData : []);
            Log::info('filterIds : ', $filterIds);

            // --- Location detection: explicit location param wins ---
            $explicitLocation = $request->filled('location') ? trim($request->get('location')) : '';
            $queryText = trim($request->get('query', ''));
            $detectedVille = null;
            $detectedQuartier = null;
            $useLatLngForProximity = true; // Default to true

            // Determine the search term: explicit location takes priority
            $term = !empty($explicitLocation) ? $explicitLocation : $queryText;

            // Try to detect quartier or ville from the term
            if (!empty($term)) {
                Log::info("Searching location with term: {$term}");
                
                // Search quartier by name (exact-ish match)
                $quartier = Quartier::where('name', 'like', '%' . $term . '%')->first();
                if ($quartier) {
                    $detectedQuartier = $quartier;
                    $useLatLngForProximity = false; // Disable proximity when specific location found
                    Log::info('Detected quartier: ' . $quartier->name);
                } else {
                    // Search ville by name
                    $ville = Ville::where('name', 'like', '%' . $term . '%')->first();
                    if ($ville) {
                        $detectedVille = $ville;
                        $useLatLngForProximity = false; // Disable proximity when specific location found
                        Log::info('Detected ville: ' . $ville->name);
                    }
                }
            } else {
                Log::info('No location term provided - will use lat/lng for proximity if available');
            }

            // Apply location-based filtering
            if ($detectedQuartier) {
                Log::info('Filtering by quartier_id: ' . $detectedQuartier->id);
                $query->where('properties.quartier_id', $detectedQuartier->id);
            } elseif ($detectedVille) {
                Log::info('Filtering by ville_id: ' . $detectedVille->id);
                $query->where('properties.ville_id', $detectedVille->id);
            }

            // --- Latitude / Longitude / Radius handling ---
            $userLat = $request->get('latitude', null);
            $userLng = $request->get('longitude', null);
            $radius = (float) $request->get('radius', 0);

            Log::info('Proximity settings:', [
                'useLatLngForProximity' => $useLatLngForProximity,
                'userLat' => $userLat,
                'userLng' => $userLng,
                'radius' => $radius
            ]);

            // Compute distance and apply proximity filtering/sorting
            if ($useLatLngForProximity && !empty($userLat) && !empty($userLng)) {
                Log::info('Computing distance from user location');
                
                // Join villes table to access latitude/longitude
                $query->join('villes', 'properties.ville_id', '=', 'villes.id');
                
                // Add condition to ensure villes have valid coordinates
                $query->whereNotNull('villes.latitude')
                    ->whereNotNull('villes.longitude');
                
                // Haversine formula using villes latitude/longitude
                $haversine = "(6371 * acos(
                    LEAST(1.0, 
                        cos(radians(?))
                        * cos(radians(villes.latitude))
                        * cos(radians(villes.longitude) - radians(?))
                        + sin(radians(?))
                        * sin(radians(villes.latitude))
                    )
                ))";

                // Add distance calculation to select
                $query->selectRaw("properties.*, $haversine AS distance", [
                    $userLat,
                    $userLng,
                    $userLat
                ]);

                // Apply radius filter if specified (radius > 0)
                if ($radius > 0) {
                    Log::info("Filtering by radius: {$radius} km");
                    $query->havingRaw("distance <= ?", [$radius]);
                }
                
                // Always order by distance when using proximity
                $query->orderBy('distance', 'asc');
            } else {
                // No proximity sorting - use default ordering
                Log::info('Using default ordering (no proximity)');
                $query->orderBy('properties.id', 'asc');
            }

            // Log the actual SQL query being executed
            $sql = $query->toSql();
            $bindings = $query->getBindings();
            Log::info('SQL Query: ' . $sql);
            Log::info('SQL Bindings: ' . json_encode($bindings));

            // Execute the query
            $properties = $query->get();

            Log::info('=== FILTERED RESULTS COUNT === ' . $properties->count());

            // Debug: If no results, let's check if properties with filter exist at all
            if ($properties->count() === 0 && !empty($filterIds)) {
                $debugCount = Property::whereHas('filterValues', function ($q) use ($filterIds) {
                    $q->whereIn('filter_option_id', $filterIds);
                })->count();
                Log::info("DEBUG: Total properties with filter (ignoring location): {$debugCount}");
                
                $debugCountWithCoords = Property::whereHas('filterValues', function ($q) use ($filterIds) {
                    $q->whereIn('filter_option_id', $filterIds);
                })
                ->join('villes', 'properties.ville_id', '=', 'villes.id')
                ->whereNotNull('villes.latitude')
                ->whereNotNull('villes.longitude')
                ->count();
                Log::info("DEBUG: Properties with filter AND valid coordinates: {$debugCountWithCoords}");
            }

            // Get ordered property IDs
            $ids = $properties->pluck('id')->values()->toArray();
            Log::info('💾 Filtered IDs (ordered): ' . json_encode($ids));

            return response()->json([
                'properties' => $properties,
                'ordered_ids' => $ids,
            ]);
            
        } catch (\Throwable $e) {
            Log::error('FILTER PROPERTIES FAILED: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage(),
            ], 500);
        }
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
