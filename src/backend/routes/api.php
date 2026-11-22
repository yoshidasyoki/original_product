<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\Me\AccountsController;
use App\Http\Controllers\Me\LearningProgressController;
use App\Http\Controllers\Household\CategoriesController;
use App\Http\Controllers\Household\TransactionsController;
use App\Http\Controllers\Investment\PastSimulationsController;
use App\Http\Controllers\Investment\ProductsController;

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
Route::post('/users', [UsersController::class, 'store'])->middleware('guest:web');
Route::get('/me', [AccountsController::class, 'index'])->middleware('auth:sanctum');
Route::patch('/me', [AccountsController::class, 'update'])->middleware('auth:sanctum');
Route::put('/me/password', [AccountsController::class, 'updatePassword'])->middleware('auth:sanctum');
Route::delete('/me', [AccountsController::class, 'destroy'])->middleware('auth:sanctum');

// 家計簿機能
Route::resource('/household/transactions', TransactionsController::class)
    ->middleware('auth:sanctum');

// 家計簿カテゴリ
Route::get('/household/categories', [CategoriesController::class, 'index']);

// 学習状況
Route::get('/me/learning-progress/curriculums', [LearningProgressController::class, 'index'])
    ->middleware('auth:sanctum');
Route::put('/me/learning-progress/curriculums', [LearningProgressController::class, 'updateAll'])
    ->middleware('auth:sanctum');
Route::get('/me/learning-progress/curriculums/{id}', [LearningProgressController::class, 'show'])
    ->middleware('auth:sanctum');
Route::patch('/me/learning-progress/curriculums/{id}', [LearningProgressController::class, 'updateOne'])
    ->middleware('auth:sanctum');

// 積立シミュレーション（過去実績）
Route::post('/investment/products/{product}/past-simulations/', [PastSimulationsController::class, 'simulate']);
Route::get('/investment/products', [ProductsController::class, 'index']);
