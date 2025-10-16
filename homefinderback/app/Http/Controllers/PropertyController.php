<?php

namespace App\Http\Controllers;

use App\Models\EntityFilterValue;
use App\Models\Image;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    // CREATE new property
    public function storeProperties(Request $request)
    {
        $owner = Auth::user();
        $rentPrice = $request->input('intention') === 'loyer' ? $request->input('rent_price') : 0;
        $salePrice = $request->input('intention') === 'vente' ? $request->input('sale_price') : 0;

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'intention'   => 'required|in:vente,loyer',
            'ville_id'    => 'required|exists:villes,id',
            'quartier_id' => 'required|exists:quartiers,id',    
            'rent_price'  => 'required_if:intention,loyer|numeric',
            'sale_price'  => 'required_if:intention,vente|numeric',        
            'images'      => 'required|array',    
            'images.*'    => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]) ;  

        $property = Property::create([
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'intention'   => $request->input('intention'),
            'ville_id'    => $request->input('ville_id'),
            'quartier_id' => $request->input('quartier_id'),
            'rent_price'  => $rentPrice,
            'sale_price'  => $salePrice,
            'owner_id'    => $owner->id,
        ]);

        // Save uploaded images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('properties', 'public');
                Image::create([
                    'url'         => $path,
                    'property_id' => $property->id,
                ]);
            } 
        }
        if ($request->has('filters')) {
            foreach ($request->filters as $categoryId => $optionId) {
                EntityFilterValue::insert([
                    'entity_id' => $property->id,           // property ID
                    'entity_type' => Property::class,       // App\Models\Property
                    'filter_option_id' => $optionId,        // the selected option
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }   
        }


        return response()->json([
            'message'  => 'Property created successfully',
            'property' => $property->load('images')
        ], 201);
    }

    // GET all properties
    public function getProperties(Request $request)
    {
        // new code 
        $query = Property::with(['ville', 'filterValues.filterOption.filterCategory']);

        if ($request->has('term')) {
            $term = $request->input('term');
            $term = strtolower($term);
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . $term . '%'])
                ->orWhereRaw('LOWER(description) LIKE ?', ['%' . $term . '%']);

        }
        if ($request->has('ville_id')) {
            $villeId = $request->input('ville_id');
            $query->where('ville_id', $villeId);
        }   
        $products = $query->orderByDesc('created_at')->paginate(4);




        // old code 
        $properties = Property::with(['images', 'ville', 'quartier'])->get();

        return response()->json([
            'properties' => $properties
        ], 200);
    }

    // GET single property or UPDATE property
    public function updateProperty(Request $request, $propertyId)
        {
            $user = Auth::user();
            $property = Property::with(['images', 'ville', 'quartier'])->findOrFail($propertyId);

            // Check ownership
            if ($property->owner_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // GET request → return property data for prefill
            if ($request->isMethod('get')) {
                return response()->json(['property' => $property], 200);
            }

            // POST request → update property
            $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'required|string',
                'intention'   => 'required|in:vente,loyer',
                'type'        => 'required|string',
                'ville_id'    => 'required|exists:villes,id',
                'quartier_id' => 'nullable|exists:quartiers,id',
                'rent_price'  => 'nullable|numeric|min:0',
                'sale_price'  => 'nullable|numeric|min:0',
                'images.*'    => 'nullable|image|max:5120',
            ]);

            $rentPrice = $request->input('intention') === 'loyer' ? $request->input('rent_price') : 0;
            $salePrice = $request->input('intention') === 'vente' ? $request->input('sale_price') : 0;

            $property->update([
                'title'       => $request->input('title'),
                'description' => $request->input('description'),
                'intention'   => $request->input('intention'),
                'type'        => $request->input('type'),
                'ville_id'    => $request->input('ville_id'),
                'quartier_id' => $request->input('quartier_id'),
                'rent_price'  => $rentPrice,
                'sale_price'  => $salePrice,
            ]);

            // Add new images if uploaded
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('properties', 'public');
                    $property->images()->create(['url' => $path]);
                }
            }

            // Return updated property with relationships
            return response()->json([
                'message'  => 'Property updated successfully',
                'property' => $property->fresh(['images', 'ville', 'quartier'])
            ], 200);
        }


        public function deleteProperty($propertyId){
            $property = Property::findOrFail($propertyId);
            $property->delete();
            return response()->json(['message' => 'Property deleted successfully'], 200);
        }   

        public function notvalidatedproperties(){
            $properties = Property::with('images', 'ville', 'quartier')->where('is_validated', false)->get(); 
            return response()->json(["properties" => $properties]) ; 
        }

        public function validateProperty($id){
            $property = Property::with(['images', 'ville', 'quartier'])->findOrFail($id);

            $property->is_validated = true;
            $property->save();

            return response()->json([
                'message' => 'Property validated successfully',
                'property' => $property
            ]);
        } 


        public function validatedproperties(){
            $properties = Property::with('images', 'ville', 'quartier')->where('is_validated', true)->get(); 
            return response()->json(["properties" => $properties]) ;
        } 


        public function getResult(Request $request){
            // Retrieve IDs from request
            $propertyIds = $request->input('propertyIds', []);

            // Fetch properties with related data
            $properties = Property::with(['images', 'ville', 'quartier'])
                ->whereIn('id', $propertyIds)
                ->get();

            return response()->json(['properties' => $properties], 200);
        } 

        public function getBySlug($slug)
        {
            $property = Property::with(['images', 'ville', 'quartier','filterValues' ,'filterOptions'])
                ->where('slug', $slug)
                ->firstOrFail();

            return response()->json(['property' => $property]);
        }




    /**
     * Sync filters with product using the generic EntityFilterValue model
     */
    private function syncPropertyFilters(Property $property, array $filterIds)
    {
        // Remove existing filter associations
        $property->filterValues()->delete();
        
        // Add new filter associations
        foreach ($filterIds as $filterId) {
            $property->filterValues()->create([
                'entity_type' => 'product',
                'filter_option_id' => $filterId
            ]);
        }
    } 


    public function getN8NProperties(Request $request)
    {
        $ids = $request->input('ids', []); // get the array from the body

        $properties = Property::whereIn('id', $ids)
            ->with(['images', 'ville', 'quartier'])
            ->get();

        return response()->json($properties);
    }

}
