<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; 

class LoginController extends Controller
{
    public function signup(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        // Create owner user
        $user = User::create([
            'name' => $request->input("name"),
            'email' => $request->input("email"),
            'password' => $request->input("password"),
            'role' => 'owner',
        ]);

        // Create token with Passport
        $tokenResult = $user->createToken('OwnerToken');
        $accessToken = $tokenResult->accessToken;

        // Return both token AND user data to frontend
        return response()->json([
            'token' => $accessToken,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]
        ], 201);
    }

public function loginowner(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    Log::info('Login attempt', ['email' => $request->input("email")]);

    $user = User::where('email', $request->input("email")) 
                 ->whereIn('role', ['owner', 'admin'])
                 ->first(); 
    
    if (!$user) {
        Log::warning('User not found or wrong role', ['email' => $request->input("email")]);
        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    // Check password based on role
    if ($user->role === 'owner') {
        if (!Hash::check($request->input("password"), $user->password)) {
            Log::warning('Password mismatch', [
                'email' => $request->input("email"),
                'hash_check_failed' => true
            ]);
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    } elseif ($user->role === 'admin') {
        if ($request->input("password") !== $user->password) {
            Log::warning('Admin password mismatch', [
                'email' => $request->input("email")
            ]);
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    }

    Log::info('User authenticated successfully', ['user_id' => $user->id]);

    // Rest of your token creation code...
    $tokenResult = $user->createToken('OwnerToken');
    $accessToken = $tokenResult->accessToken;
    
    return response()->json([
        'token' => $accessToken,
        'token_type' => 'Bearer',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ]
    ], 200);
}




    public function verifyToken(Request $request){
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
        
        // Return user data without sensitive information
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ], 200);
    }

    // Optional: Logout function if needed
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        
        return response()->json([
            'message' => 'Successfully logged out'
        ], 200);
    }

    public function signupuser(Request $request){
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        
        $user = User::create([
            'name' => $request->input("name"),
            'email' => $request->input("email"),
            'password' => $request->input("password"),
            'role' => 'user',
        ]);

        // Create token with Passport
        $tokenResult = $user->createToken('OwnerToken');
        $accessToken = $tokenResult->accessToken;

        // Return both token AND user data to frontend
        return response()->json([
            'token' => $accessToken,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]
        ], 201);
    }        

    public function loginuser(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);


    $user = User::where('email', $request->input("email")) 
                 ->where('role', 'user')
                 ->first(); 
    
    if (!$user) {
        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    // Check password based on role
    if ($user->role === 'user') {
        if (!Hash::check($request->input("password"), $user->password)) {
            Log::warning('Password mismatch', [
                'email' => $request->input("email"),
                'hash_check_failed' => true
            ]);
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    }


    // Rest of your token creation code...
    $tokenResult = $user->createToken('OwnerToken');
    $accessToken = $tokenResult->accessToken;
    
    return response()->json([
        'token' => $accessToken,
        'token_type' => 'Bearer',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ]
    ], 200);
}
    
}