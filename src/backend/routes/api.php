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

// ヘルスチェック
Route::get('/health', function () {
    return response()->json(['status' => 'OK'], 200);
});

// ---- ゲスト専用 ----
Route::middleware('guest:web')->group(function () {
    // ログイン認証
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login');

    // ユーザー登録
    Route::post('/users', [UsersController::class, 'store'])
        ->middleware('throttle:account-register');
});

// ---- 認証が必要な処理 ----
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // ログアウト
    Route::post('/logout', [AuthController::class, 'logout']);

    // アカウント関連
    Route::controller(AccountsController::class)->group(function () {
        Route::get('/me', 'index');
        Route::patch('/me', 'update');
        Route::put('/me/password', 'updatePassword');
        Route::delete('/me', 'destroy');
    });

    // 家計簿機能
    Route::apiResource('/household/transactions', TransactionsController::class);

    // 学習状況
    Route::controller(LearningProgressController::class)->group(function () {
        Route::get('/me/learning-progress/curriculums', 'index');
        Route::put('/me/learning-progress/curriculums', 'updateAll');
        Route::get('/me/learning-progress/curriculums/{id}', 'show');
        Route::patch('/me/learning-progress/curriculums/{id}', 'updateOne');
    });
});

// ---- 認証状態に関わらず実行可能な処理 ----
// 家計簿カテゴリ
Route::get('/household/categories', [CategoriesController::class, 'index'])
    ->middleware('throttle:api');

// 積立シミュレーション（過去実績）
Route::post('/investment/products/{product}/past-simulations/', [PastSimulationsController::class, 'simulate'])
    ->middleware('throttle:simulate');
Route::get('/investment/products', [ProductsController::class, 'index'])
    ->middleware('throttle:api');
