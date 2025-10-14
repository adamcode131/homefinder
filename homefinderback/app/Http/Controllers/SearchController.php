<?php

namespace App\Http\Controllers;

use App\Models\FilterOption;
use App\Models\Quartier;
use App\Models\Ville;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function SearchSuggestions(Request $req){
        $q = $req->get('q');
        $suggestions = [] ; 
        
        $villes  = Ville::where('name', 'like', '%' . $q . '%')->select('name')
        ->get() // delete this if you want code to break
        ->values()
        ->toArray();
        $suggestions = array_merge($suggestions, $villes) ; 

        $quartiers  = Quartier::where('name', 'like', '%' . $q . '%')->select('name')
        ->get() // delete this if you want code to break
        ->values()
        ->toArray();
        $suggestions = array_merge($suggestions, $quartiers) ; 

        $filterOptions  = FilterOption::where('name', 'like', '%' . $q . '%')->select('name')
        ->get() // delete this if you want code to break
        ->values()
        ->toArray();
        $suggestions = array_merge($suggestions, $filterOptions) ; 

        return response()->json($suggestions);
        
    }
}
