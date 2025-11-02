<?php

namespace App\Http\Controllers;

use App\Models\CachedResult;
use App\Models\FilterCategory;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CacheController extends Controller
{
    // Retrieve cached results (query + proximity check)
public function getCachedResults(Request $request)
{
    $query = trim(strtolower($request->get('query')));
    $userLat = $request->get('latitude');
    $userLng = $request->get('longitude');

    Log::info('=== GET CACHED RESULTS REQUEST ===', [
        'query' => $query,
        'latitude' => $userLat,
        'longitude' => $userLng
    ]);

    if (!$query) {
        return response()->json([
            'ids' => [],
            'latitude' => $userLat,
            'longitude' => $userLng,
            'from_cache' => false
        ]);
    }

    // Base query: partial match on query
    $cacheQuery = CachedResult::whereRaw('LOWER(query) LIKE ?', ["%{$query}%"]);

    // If coordinates exist, calculate distance but do NOT exclude far results
    if ($userLat && $userLng) {
        $cacheQuery = $cacheQuery
            ->select('*')
            ->selectRaw(
                "(6371 * acos(
                    cos(radians(?)) 
                    * cos(radians(latitude)) 
                    * cos(radians(longitude) - radians(?)) 
                    + sin(radians(?)) 
                    * sin(radians(latitude))
                )) AS distance",
                [$userLat, $userLng, $userLat]
            )
            ->orderBy('distance', 'asc');
    }

    $cached = $cacheQuery->first();

    if ($cached) {
        Log::info('✅ Cache hit', [
            'cached_query' => $cached->query,
            'cached_lat' => $cached->latitude,
            'cached_lng' => $cached->longitude,
        ]);

        $ids = $cached->properties_ids;
        if (is_string($ids)) {
            $ids = json_decode($ids, true);
        }

        return response()->json([
            'ids' => $ids ?? [],
            'latitude' => $cached->latitude,
            'longitude' => $cached->longitude,
            'from_cache' => true
        ]);
    }


    Log::info('❌ Cache miss (no cached query found)');
    return response()->json([
        'ids' => [],
        'latitude' => $userLat,
        'longitude' => $userLng,
        'from_cache' => false
    ]);
}


/**
 * Helper: return all filter categories with active options
 */
private function getAllFilterOptions()
{
    return FilterCategory::with(['activeOptions'])->where('is_active', true)->get();
}



    // Save cached results with coordinates
    public function saveCachedResults(Request $request)
    {
        $query = trim(strtolower($request->get('query')));
        $ids = $request->get('ids');
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');

        if (!$query || empty($ids)) {
            return response()->json(['message' => 'Invalid cache data'], 400);
        }

        $cache = CachedResult::updateOrCreate(
            ['query' => $query],
            [
                'properties_ids' => json_encode($ids),
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]
        );

        Log::info('💾 Cached results saved', [
            'query' => $query,
            'ids' => $ids,
            'latitude' => $latitude,
            'longitude' => $longitude
        ]);

        return response()->json(['message' => 'Cached successfully']);
    }
}
