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
    $propertyIds = $request->input('properties', []);
    
    foreach ($propertyIds as $propertyId) {
        $property = Property::findOrFail($propertyId);
        
        if (auth()->check()) {
            // AUTHENTICATED USER - Use database data
            $user = auth()->user();
            
            Lead::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? null, // Get from user table if exists
                'property_id' => $propertyId,
                'owner_id' => $property->owner_id,
                'status' => 'pending',
                'date_reservation' => $request->input('date_reservation'),
            ]);
        } else {
            // UNAUTHENTICATED USER - Use form data
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:20',
                'date_reservation' => 'required|date',
            ]);
            
            Lead::create([
                'user_id' => null,
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'property_id' => $propertyId,
                'owner_id' => $property->owner_id,
                'status' => 'pending',
                'date_reservation' => $request->input('date_reservation'),
            ]);
        }
    }

    return response()->json(['message' => 'Reservation confirmed successfully'], 200);
}

public function getLeads(){
    $user = auth()->user();
    $leads = Lead::where('owner_id', $user->id)
    ->with('property')
    ->get();
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

    public function addLeadNonAuth(Request $request , $propertyId){ 
        $property = Property::findOrFail($propertyId);
            $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'date_reservation' => 'required|date',
            ]);
            
            Lead::create([
                'user_id' => null,
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'property_id' => $propertyId,
                'owner_id' => $property->owner_id,
                'status' => 'pending',
                'date_reservation' => $request->input('date_reservation'),
            ]);
    }
}


