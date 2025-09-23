<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUsers(){
        $users = User::where('role', 'user')->get();
        return response()->json(['users' => $users]);
    } 

    public function getUser(){
        $user = auth()->user();
        return response()->json(['user' => $user]);
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

}
