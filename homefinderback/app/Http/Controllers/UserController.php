<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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

}
