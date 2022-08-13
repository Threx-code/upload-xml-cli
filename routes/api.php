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

Route::get('authenticate/google', [XMLController::class, 'authURL']);
Route::get('authenticate/users', [XMLController::class, 'auththentication']);
//Route::get('authenticate/test', [XMLController::class, 'revalidateAccessToken'])->middleware('auth.api');

Route::middleware('auth:api')->group(function(){
    Route::get('authenticate/test', [XMLController::class, 'revalidateAccessToken']);
});
