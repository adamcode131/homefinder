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

        $term = isset($request->term) ? $request->term : trim($request->term);
        $quartier = Quartier::where('name', 'like', '%' . $term . '%')->first();
        if($quartier)
            return response()->json(['quartier' => $quartier]);
        else
        {
            $ville = Ville::where('name', 'like', '%' . $term . '%')->first();
                if($ville)
                    return response()->json(['ville' => $ville]);
        }
           
        return response()->json(['message' => 'Ville not found'], 404);


        if ($ville && $quartier) {
            // Case: both ville and quartier provided
            $villeData = Ville::where('name', $ville)
                ->with(['quartiers' => function($q) use ($quartier) {
                    $q->where('name', $quartier);
                }])
                ->first();
        } elseif ($ville) {
            // Case: only ville provided
            $villeData = Ville::where('name', $ville)
                ->with('quartiers')
                ->first();
        } else {
            // No ville provided — should not happen due to route
            return response()->json(['message' => 'Ville not specified'], 400);
        }
    
        return response()->json($villeData);
    }
    
    
}
