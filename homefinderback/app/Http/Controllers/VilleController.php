<?php

namespace App\Http\Controllers;

use App\Models\Quartier;
use App\Models\Ville;
use Illuminate\Http\Request;

class VilleController extends Controller
{
    // 

    public function getVilles()
    {
        $villes = Ville::all() ; 
        return response()->json($villes);
    } 

    public function getQuartiersByVille($villeid)
    {
        $ville = Ville::with('quartiers')->find($villeid);

        if (!$ville) {
            return response()->json(['error' => 'Ville not found'], 404);
        }

        return response()->json($ville->quartiers);
    } 


public function getVilleAndQuartier(Request $request)
{
    $term = trim($request->get('term', ''));

    if (!$term) {
        // No term provided — return empty
        return response()->json(['term' => null, 'ville' => null, 'quartier' => null]);
    }

    // Try to find a quartier
    $quartier = Quartier::where('name', 'like', '%' . $term . '%')->first();
    if ($quartier) {
        return response()->json([
            'term' => $term,
            'quartier' => $quartier,
            'ville' => $quartier->ville ?? null // if you have relation
        ]);
    }

    // Try to find a ville
    $ville = Ville::where('name', 'like', '%' . $term . '%')->first();
    if ($ville) {
        return response()->json([
            'term' => $term,
            'ville' => $ville,
            'quartier' => null
        ]);
    }

    // Nothing found — just return what was sent
    return response()->json([
        'term' => $term,
        'ville' => null,
        'quartier' => null
    ]);
}

    
    
}
