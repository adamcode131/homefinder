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
}
