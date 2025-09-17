<?php

namespace App\Http\Controllers;

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
}
