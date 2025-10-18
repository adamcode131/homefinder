<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
 public function addNotification(Request $req, $id)
    {
        
        $user = User::findOrFail($id);
        
        // Validate the request
        $validated = $req->validate([
            'added_points' => 'required|integer|min:0',
            'deducted_points' => 'required|integer|min:0',
        ]);

        // Only create notification if there are points to add/deduct
        if ($validated['added_points'] > 0 || $validated['deducted_points'] > 0) {
            Notification::create([
                'owner_id' => $user->id,
                'added_points' => $validated['added_points'],
                'deducted_points' => $validated['deducted_points']
            ]);
        }

        return response()->json([
            "message" => "Notification added successfully",
            "added_points" => $validated['added_points'],
            "deducted_points" => $validated['deducted_points']
        ]);


    }

    public function getNotifications(){
        $user = auth()->user() ; 
        $notifications = Notification::where('owner_id',$user->id)->get() ; 
        return response()->json($notifications);
    }
}
