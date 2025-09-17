<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    public function storeProperties(Request $request)
    {
        $rentPrice = null;
        $salePrice = null;
        $owner = Auth::user();
         
        if ($request->input('intention') === 'loyer') {
            $rentPrice = $request->input('rent_price');
            $salePrice = 0;
        } elseif ($request->input('intention') === 'vente') {
            $salePrice = $request->input('sale_price');
            $rentPrice = 0;
        }

        $property = Property::create([
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'intention'   => $request->input('intention'),
            'type'        => $request->input('type'),
            'ville_id'    => $request->input('ville_id'),
            'quartier_id' => $request->input('quartier_id'),
            'rent_price'  => $rentPrice,
            'sale_price'  => $salePrice,
            'owner_id'    => $owner->id,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('properties', 'public');
                
                Image::create([
                    'url'         => $path,
                    'property_id' => $property->id,
                ]);
            }
        }

        return response()->json([
            'message'  => 'Property created successfully',
            'property' => $property->load('images')
        ], 201);
    }

    public function getProperties()
    {
        $properties = Property::with('images')->get();

        return response()->json([
            'properties' => $properties
        ], 200);
    }
}
