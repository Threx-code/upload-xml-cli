<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\XMLController;

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

Route::get('authenticate/google', [XMLController::class, 'getAuthUrl']);
Route::get('authenticate/users', [XMLController::class, 'authenticateUser']);
Route::middleware('auth:api')->group(function(){
    Route::get('upload/xml', [XMLController::class, 'uploadXMLFile']);
});
