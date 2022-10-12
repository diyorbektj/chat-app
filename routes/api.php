<?php

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


Route::controller(\App\Http\Controllers\AuthController::class)
    ->prefix('auth')
    ->as('auth.')
    ->group(function (){
        Route::post('/login', 'login')->name('login');
        Route::post('register', 'register')->name('register');
        Route::middleware('auth:sanctum')->group(function (){
            Route::post('/login-with-token', 'loginWithToken')->name('login-woth-token');
            Route::get('/logout', 'logout')->name('logout');
        });
    });
Route::middleware('auth:sanctum')->group(function (){
    Route::apiResource('chat', \App\Http\Controllers\ChatController::class)->only(['index','store','show']);
    Route::apiResource('chat-message', \App\Http\Controllers\ChatMessageController::class)->only(['index','store']);
    Route::apiResource('user', \App\Http\Controllers\UserController::class)->only(['index']);
});
