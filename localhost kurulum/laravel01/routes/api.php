<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Models\Device;
use App\Models\DeviceP;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('/register', [UserController::class,"upload"]);
Route::post('/purchase', [UserController::class,"purchase"]);
Route::post('/check', [UserController::class,"check"]);
Route::get('/info', [UserController::class,"getinfo"]);







