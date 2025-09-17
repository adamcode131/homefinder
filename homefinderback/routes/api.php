<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\VilleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/signupowner', [LoginController::class, 'signup']);
Route::post('/loginowner', [LoginController::class, 'loginowner']);
Route::middleware('auth:api')->get('/verify-token', [LoginController::class, 'verifyToken']);
Route::middleware('auth:api')->post('/logout', [LoginController::class, 'logout']); 
Route::get('/villes', [VilleController::class, 'getVilles']); 
Route::get('/villes/{villeid}/quartiers', [VilleController::class, 'getQuartiersByVille']); 
Route::middleware('auth:api')->post('/storeProperties', [PropertyController::class, 'storeProperties']);
Route::post('/properties', [PropertyController::class, 'getProperties']);