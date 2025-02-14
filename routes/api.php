<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\authController;
use App\Http\Controllers\api\MessageController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

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
Route::post('/login', [authController::class, 'login']);
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/search-user', [MessageController::class, 'searchUser']);
    Route::get('/friends', [MessageController::class, 'friends']);
    Route::post('/get-messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/logout', function(Request $request) {
        if(Auth::check()) {
            $request->user()->currentAccessToken()->delete();
            // Auth::logout();
            return response()->json([
                'status' => true,
            ]);
        }
        return response()->json([
            'status' => false,
        ]);
    });
});

Route::match(['get','post'], '/broadcasting/auth', function (User $user) {
    return Auth::user();
});

