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
    $propertyIds = $request->input('properties', []); // array of IDs

    foreach ($propertyIds as $propertyId) {
        $property = Property::findOrFail($propertyId);

        if (auth()->user()) {
            Lead::create([
                'user_id' => auth()->user()->id,
                'property_id' => $propertyId,
                'owner_id' => $property->owner_id,
                'status' => 'pending',
                'date_reservation' => $request->input('date_reservation'),
            ]);
        } else {
            Lead::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'date_reservation' => $request->input('date_reservation'),
                'property_id' => $propertyId,
                'owner_id' => $property->owner_id,
            ]);
        }
    }

    return response()->json(['message' => 'Reservation confirmed successfully'], 200);
} 

public function getLeads(){
    $leads = Lead::all();
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

        return response()->json([
            'message' => 'Lead accepted successfully',
            'lead' => $lead,
            'user' => $user // optional if you want to return updated points
        ]);
    }
}

