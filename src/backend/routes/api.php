<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Me\AccountController;

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
Route::get('/me', [AccountController::class, 'me'])->middleware('auth:sanctum');
Route::put('/me', [AccountController::class, 'update'])->middleware('auth:sanctum');
Route::put('/me/password', [AccountController::class, 'updatePassword'])->middleware('auth:sanctum');
Route::delete('/me', [AccountController::class, 'destroy'])->middleware('auth:sanctum');
