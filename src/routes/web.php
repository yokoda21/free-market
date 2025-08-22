<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PurchaseController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 商品一覧画面（トップ画面）
Route::get('/', [ItemController::class, 'index'])->name('items.index');

// 商品一覧画面（マイリスト）
Route::get('/?tab=mylist', [ItemController::class, 'index'])->name('items.mylist');

// 商品検索（追加）
Route::get('/search', [ItemController::class, 'search'])->name('items.search');

// 会員登録、ログイン画面はFortifyを使用


// 商品詳細画面
Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('items.show');

// 商品購入画面
Route::get('/purchase/{item_id}', [PurchaseController::class, 'show'])->name('purchase.show');

// 住所変更ページ
Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])->name('purchase.address');

// 商品出品画面
Route::get('/sell', [ItemController::class, 'create'])->name('items.create');

// プロフィール画面
Route::get('/mypage', [UserController::class, 'profile'])->name('user.profile');

// プロフィール編集画面
Route::get('/mypage/profile', [UserController::class, 'editProfile'])->name('user.profile.edit');

// プロフィール画面_購入した商品一覧
Route::get('/mypage?page=buy', [UserController::class, 'profile'])->name('user.profile.buy');

// プロフィール画面_出品した商品一覧
Route::get('/mypage?page=sell', [UserController::class, 'profile'])->name('user.profile.sell');

// 認証が必要なルート
Route::middleware(['auth'])->group(function () {

    // いいね機能（Ajax）
    Route::post('/items/{item}/like', [ItemController::class, 'toggleLike'])->name('items.like');

    // コメント投稿
    Route::post('/item/{item}/comment', [ItemController::class, 'storeComment'])->name('items.comment');

    // 商品出品処理
    Route::post('/sell', [ItemController::class, 'store'])->name('items.store');

    // 購入処理
    Route::post('/purchase/{item}', [PurchaseController::class, 'store'])->name('purchase.store');

    // 住所変更処理
    Route::put('/purchase/address/{item}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');

    // プロフィール更新処理
    Route::put('/mypage/profile', [UserController::class, 'updateProfile'])->name('user.profile.update');
});
