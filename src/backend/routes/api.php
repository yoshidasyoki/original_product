<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('/health', function () {
    return response()->json(['status' => 'OK'], 200);
});

// ログイン認証
Route::post('/login', [AuthController::class, 'login'])->middleware('guest:web');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// アカウント関連
Route::post('/users', [UserController::class, 'store'])->middleware('guest:web');
Route::get('/me', [UserController::class, 'me'])->middleware('auth:sanctum');
Route::put('/me', [UserController::class, 'update'])->middleware('auth:sanctum');
Route::put('/me/password', [UserController::class, 'updatePassword'])->middleware('auth:sanctum');
Route::delete('/me', [UserController::class, 'destroy'])->middleware('auth:sanctum');
