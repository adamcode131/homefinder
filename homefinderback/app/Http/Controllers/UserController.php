<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log ; 

class UserController extends Controller
{
    public function getUsers(){
        $users = User::all();
        return response()->json(['users' => $users]);
    } 

    public function getUser(){
        $user = auth()->user();
        if($user){
        return response()->json(['success'=>true,'user' => $user]);
        }
        else{
            return response()->json(['success'=>false]);
        }
    } 
     
    public function updateBalance() {
        $user = auth()->user();
        $user = User::find($user->id);

        if ($user->balance <= 0) {
            return response()->json(['message' => 'Not enough points'], 400);
        }

        $user->balance -= 1;
        $user->save();

        return response()->json(['user' => $user]);
    } 

    public function updateUser(Request $request){
        $user = auth()->user()->id ; 
        $user = User::find($user); 
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'phone' => 'nullable|string',
            'password' => 'nullable|string|min:8'
        ]) ;
        $user->update([
            'name' => $request->name ,
            'email' => $request->email ,  
            'phone' => $request->phone ,
        ]); 

        if ($request->filled('password')){
            $user->update([
                'password' => bcrypt($request->password),
            ]);
        }
    }


    public function updateUserFromAdminPanel(Request $req , $user_id){
        
        $user = User::findOrFail($user_id);

        $validated = $req->validate([
            "name" => "required" , 
            "email" => "required|email" , 
            "phone" => "required" , 
            "role" => "required" , 
            "balance" => "required|numeric" , 
        ]);  

        $user->update($validated) ;
        $user->save() ; 
        return response()->json(["message"=>"user updated successfully"], 200) ; 

    }



public function updateProfile(Request $request){
    // Log the request properly - FIXED LOGGING
    Log::info('PROFILE REQUEST: ' . json_encode($request->all()));
    Log::info('REQUEST HEADERS: ' . json_encode($request->headers->all()));
    
    // Check if user is authenticated
    $authUser = auth()->user();
    
    if (!$authUser) {
        Log::error('User not authenticated');
        return response()->json([
            'message' => 'Unauthenticated. Please log in again.'
        ], 401);
    }
    
    Log::info('Authenticated user ID: ' . $authUser->id);
    
    $user = User::find($authUser->id);
    
    if (!$user) {
        Log::error('User not found in database with ID: ' . $authUser->id);
        return response()->json([
            'message' => 'User not found'
        ], 404);
    }
    
    $validated = $request->validate([
        'name' => 'nullable|string', 
        'phone' => 'nullable|string',
    ]); 
    
    Log::info('VALIDATED DATA: ' . json_encode($validated));
    
    if ($request->hasFile('image')){
        Log::info('Image file detected: ' . $request->file('image')->getClientOriginalName());
        $path = $request->file('image')->store('users','public');
        $validated['image'] = $path; 
        Log::info('Image stored at: ' . $path);
    } else {
        Log::info('No image file in request');
    }   
    
    $user->update($validated);
    
    return response()->json([
        'message' => 'User updated successfully!', 
        'user' => $user->fresh()
    ]);
}

}
