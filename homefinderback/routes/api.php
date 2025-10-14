<?php

use App\Http\Controllers\Admin\FilterCategoryController;
use App\Http\Controllers\Admin\FilterOptionController;
use App\Http\Controllers\Client\PropertyFilterController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VilleController;
use App\Http\Controllers\TestController;
use App\Models\Property;
use App\Models\Refund;
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
Route::get('/ville/{ville}/{quartier?}', [VilleController::class, 'getVilleAndQuartier']); // n8n
Route::get('/research-cities', [VilleController::class, 'getVilleAndQuartier']); // n8n
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
Route::put('/update_user', [UserController::class, 'updateUser'])->middleware("auth:api");
Route::post('/leads', [LeadController::class, 'addLead'])->middleware('auth:api'); 
Route::get('/all_leads', [LeadController::class, 'getLeads'])->middleware('auth:api'); // this one for admin
Route::get('/all_leads', [LeadController::class, 'getAllLeads'])->middleware('auth:api'); // this one for owner
Route::post('/updateBalance', [UserController::class, 'updateBalance'])->middleware('auth:api') ; 
Route::post('/leads/{lead}/accept', [LeadController::class, 'acceptLead'])->middleware('auth:api') ; 
Route::post('/result', [PropertyController::class, 'getResult']);
Route::post('/details/{slug}', [PropertyController::class, 'getBySlug']);
Route::post('/addLead/{propertyId}', [LeadController::class, 'addLeadNonAuth']);
Route::post('/refund/{leadId}' , [RefundController::class , 'addRefund']);
Route::get('/refund-reasons' , [RefundController::class , 'getReasons']);
Route::get('/all-refunds' , [RefundController::class , 'getAllRefunds']);
Route::post('/accept-refund/{refundId}' , [RefundController::class , 'acceptRefund']);
// for suggestion search in home
Route::get('/search-suggestions', [SearchController::class, 'SearchSuggestions']);


// routes for filters

Route::middleware('auth:api')->group(function () {

        // property CRUD API routes
        // Route::apiResource('properties', PropertyController::class);

        
        // property filters routes
        Route::get('property-filters', [PropertyFilterController::class, 'getFilters']);
        Route::post('filter-properties', [PropertyFilterController::class, 'filterProperties']);
        Route::get('property-filters/stats', [PropertyFilterController::class, 'getFilterStats']);


        // Admin Filter Management routes
        Route::prefix('admin')->group(function () {
            // Filter Categories CRUD
            Route::apiResource('filter-categories', FilterCategoryController::class);
            
            
            // Filter Options CRUD  
            Route::apiResource('filter-options', FilterOptionController::class);
            Route::get('filter-options/category/{categoryId}', [FilterOptionController::class, 'getByCategory']);
        })->middleware('auth:api') ; 


});






