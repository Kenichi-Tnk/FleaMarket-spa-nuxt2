<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ConditionController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\ProfileController;

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

// 認証関連
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 商品関連（認証不要）
Route::get('/items', [ItemController::class, 'index']);
Route::get('/items/{id}', [ItemController::class, 'show']);

// コメント取得（認証不要）
Route::get('/items/{itemId}/comments', [CommentController::class, 'index']);

// マスターデータ（認証不要）
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/conditions', [ConditionController::class, 'index']);

// 認証が必要なルート
Route::middleware('auth:api')->group(function () {
    // 認証
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    // プロフィール
    Route::post('/profile', [ProfileController::class, 'update']);

    // 商品
    Route::post('/items', [ItemController::class, 'store']);
    Route::put('/items/{id}', [ItemController::class, 'update']);
    Route::delete('/items/{id}', [ItemController::class, 'destroy']);

    // お気に入り
    Route::post('/items/{itemId}/favorite', [FavoriteController::class, 'toggle']);
    Route::get('/favorites', [FavoriteController::class, 'index']);

    // コメント
    Route::post('/items/{itemId}/comments', [CommentController::class, 'store']);
    Route::delete('/items/{itemId}/comments/{commentId}', [CommentController::class, 'destroy']);

    // 購入
    Route::post('/items/{itemId}/purchase', [PurchaseController::class, 'store']);
    Route::get('/purchases', [PurchaseController::class, 'index']);
    Route::get('/purchases/{id}', [PurchaseController::class, 'show']);
});
