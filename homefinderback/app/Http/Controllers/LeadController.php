<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Property;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    // 
public function addLead(Request $request)
{
    $propertySlugs = $request->input('properties', []);

    foreach ($propertySlugs as $slug) {
        // Find property by slug instead of ID
        $property = Property::where('slug', $slug)->firstOrFail();

        if (auth()->check()) {
            $user = auth()->user();

            $lead = Lead::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? null,
                'property_id' => $property->id, // use the property ID internally
                'owner_id' => $property->owner_id,
                'status' => 'pending',
                'date_reservation' => $request->input('date_reservation'),
            ]);
        } 
        // else {
        //     // UNAUTHENTICATED USER - Use form data
        //     $request->validate([
        //         'name' => 'required|string|max:255',
        //         'email' => 'required|email|max:255',
        //         'phone' => 'nullable|string|max:20',
        //         'date_reservation' => 'required|date',
        //     ]);

        //     Lead::create([
        //         'user_id' => null,
        //         'name' => $request->input('name'),
        //         'email' => $request->input('email'),
        //         'phone' => $request->input('phone'),
        //         'property_id' => $property->id, // still store the ID
        //         'owner_id' => $property->owner_id,
        //         'status' => 'pending',
        //         'date_reservation' => $request->input('date_reservation'),
        //     ]);
        // } 

        
    }

    return response()->json(['message' => 'Reservation confirmed successfully' , 'lead_id' => $lead->id ], 200);
}


public function getLeads(){
    $user = auth()->user();
    $leads = Lead::with('property')->where('user_id', $user->id)->get();
    return response()->json(['leads' => $leads]);
    
}  

public function getAllLeads(){
    $owner = auth()->user();
    $leads = Lead::with('property')->where('owner_id', $owner->id)->get();
    return response()->json(['leads' => $leads]);
}

public function acceptLead(Lead $lead, Request $request)
    {
        // Optional: check if the user has enough points, etc.
        $user = $request->user();

        if ($lead->status !== 'pending') {
            return response()->json(['message' => 'Lead already accepted'], 400);
        }

        $lead->status = 'accepted';
        $lead->save(); 
        // reduce the user points by 1
        $user->balance -= 1;
        $user->save();


        return response()->json([
            'message' => 'Lead accepted successfully',
            'lead' => $lead,
            'user' => $user // optional if you want to return updated points
        ]);
    } 

    public function addLeadNonAuth(Request $request , $slug){ 
        $property = Property::where('slug',$slug)->first();
            $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'date_reservation' => 'required|date',
            ]);
            
            $lead = Lead::create([
                'user_id' => null,
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'property_id' => $property->id,
                'owner_id' => $property->owner_id,
                'status' => 'pending',
                'date_reservation' => $request->input('date_reservation'),
            ]);
            return response(['message'=>'lead added successfully' , 'lead_id' => $lead->id ],200) ; 
    }
}


