<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class TestController extends Controller
{
    
        public function signup(Request $request){
        $user = User::create([
            'name' => $request->input("name") , 
            'email' => $request->input("email") ,
            'password' => $request->input("password") ,
        ]) ; 

        $token = $user->createToken('token')->accessToken ; 
        return response()->json([
            'token'=>$token
        ]) ; 
        }


        public function login($token){

            $user = auth()->user() ; 

            if($user){
                return response()->json([
                    'token'=>$token
                ],201) ;
            }   
            

    }
}