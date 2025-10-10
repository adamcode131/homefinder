<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function addRefund(Request $request , $id){
        $lead = Lead::findOrFail($id) ; 

        $request->validate([
            'reason' => 'required|string'
        ]);

        Refund::create([
            'lead_id' => $lead->id,
            'reason' => $request->input('reason'),
        ]); 

        return response()->json([
            'message' => 'Refund added successfully'
        ]);

    } 

    public function getReasons(){
        $reasons = Refund::select('reason')->get();
        return response()->json([
            'reasons' => $reasons
        ]);
    } 

    public function getAllRefunds () {
        $refunds = Refund::with(['lead.property' , 'lead.owner'])->get(); 
        return response()->json([
            "refunds" => $refunds 
        ]) ; 
    }


    public function acceptRefund($refundId){
        $refund = Refund::findOrFail($refundId) ; 
        // get the owner of the lead who requested the refund
        $owner_id = $refund->lead->owner_id;
        $owner = User::find($owner_id) ;
        $owner->balance += 1 ; 
        $owner->save() ;
        $refund->status = 'completed' ; 
        $refund->save() ; 
        return response()->json([
            'message' => 'Refund accepted successfully'
        ]);
    }
}
