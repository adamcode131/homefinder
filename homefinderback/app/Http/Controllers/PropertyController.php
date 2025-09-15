<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function storeProperties(Request $request, $ownerid){
        
        $rentPrice = null;
        $salePrice = null;

        if ($request->input('purpose') === 'rent') {
            $rentPrice = $request->input('rent_price');
            $salePrice = 0;
        } elseif ($request->input('purpose') === 'sale') {
            $salePrice = $request->input('sale_price');
            $rentPrice = 0;
        }

        // Create the property
        $property = Property::create([
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'purpose'     => $request->input('purpose'),
            'type'        => $request->input('type'),
            'ville'       => $request->input('ville'),
            'quartier'    => $request->input('quartier'),
            'rent_price'  => $rentPrice,
            'sale_price'  => $salePrice,
            'owner_id'    => $ownerid,
        ]);

        // Handle images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('properties', 'public'); // storage/app/public/properties

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

public function getProperties($ownerid)
{
    $properties = Property::where('owner_id', $ownerid)
                          ->with('images') // eager load images
                          ->get();

    return response()->json([
        'properties' => $properties
    ], 200);
}



} 



