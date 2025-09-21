<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VilleController;
use App\Http\Controllers\TestController;

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
Route::get('/properties', [PropertyController::class, 'getProperties']);
Route::get('/updateproperty/{propertyId}', [PropertyController::class, 'updateProperty'])->middleware('auth:api');
Route::post('/updateproperty/{propertyId}', [PropertyController::class, 'updateProperty'])->middleware('auth:api');
Route::delete('/deleteproperty/{propertyId}', [PropertyController::class, 'deleteProperty'])->middleware('auth:api');
Route::post('/signupuser', [LoginController::class, 'signupuser']);
Route::post('/loginuser', [LoginController::class, 'loginuser']);
Route::get('/notvalidatedproperties', [PropertyController::class, 'notvalidatedproperties']);
Route::get('/validatedproperties', [PropertyController::class, 'validatedproperties']);
Route::patch('/properties/{id}/validate', [PropertyController::class, 'validateProperty']);
Route::get('/users', [UserController::class, 'getUsers']); 
Route::get('/user', [UserController::class, 'getUser'])->middleware("auth:api"); 




// testing
Route::post('/createUser',[TestController::class,'signup']) ;  
Route::post('/logintest',[TestController::class,'login']) ;  
