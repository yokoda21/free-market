<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\Auth\EmailVerificationController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

// ===== パブリックルート（認証不要） =====
Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/search', [ItemController::class, 'index'])->name('items.search');
Route::get('/item/{item}', [ItemController::class, 'show'])->name('items.show');

// ===== 認証関連ルート =====
// Fortifyのデフォルトルート + カスタムルート

// 会員登録処理（RegisterRequest適用）
Route::post('/register', [UserController::class, 'register'])->name('register.custom');

// ログイン処理（LoginRequest適用）
Route::post('/login', [UserController::class, 'login'])->name('login.custom');

// ===== メール認証関連ルート =====
Route::middleware('auth')->group(function () {
    // メール認証誘導画面
    Route::get('/email/verify', [App\Http\Controllers\Auth\EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    // メール認証完了処理（プロフィール編集画面へ遷移）
    Route::post('/email/verify/complete', [App\Http\Controllers\Auth\EmailVerificationController::class, 'complete'])
        ->name('verification.complete');

    // メール認証実行（メール内リンク用 - Mailhog確認用のみ）
    Route::get('/email/verify/{id}/{hash}', function (Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('user.edit-profile')->with('success', 'メールアドレスの認証が完了しました');
    })->middleware('signed')->name('verification.verify');

    // 認証メール再送信
    Route::post('/email/verification-notification', [App\Http\Controllers\Auth\EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// ===== 認証必須ルート（メール認証も必須） =====
Route::middleware(['auth', 'verified'])->group(function () {
    // 商品出品
    Route::get('/sell', [ItemController::class, 'create'])->name('items.create');
    Route::post('/sell', [ItemController::class, 'store'])->name('items.store');

    // いいね機能
    Route::post('/items/{item}/like', [ItemController::class, 'toggleLike'])->name('items.like');

    // コメント機能
    Route::post('/item/{item}/comment', [ItemController::class, 'storeComment'])->name('items.comment');

    // 購入関連
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'show'])->name('purchase.show');
    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])->name('purchase.address');
    Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');
    Route::post('/purchase/{item_id}', [PurchaseController::class, 'store'])->name('purchase.store');

    // 取引チャット関連
    Route::get('/trades/{purchase}', [TradeController::class, 'show'])->name('trades.show');
    Route::post('/trades/{purchase}/messages', [TradeController::class, 'storeMessage'])->name('trades.messages.store');
    Route::patch('/trades/messages/{message}', [TradeController::class, 'updateMessage'])->name('trades.messages.update');
    Route::delete('/trades/messages/{message}', [TradeController::class, 'destroyMessage'])->name('trades.messages.destroy');
    Route::post('/trades/{purchase}/save-input', [TradeController::class, 'saveInput'])->name('trades.save-input');
    Route::post('/trades/{purchase}/complete', [TradeController::class, 'complete'])->name('trades.complete');

    // 評価関連
    Route::post('/trades/{purchase}/rate', [RatingController::class, 'store'])->name('ratings.store');

    // ユーザー・プロフィール関連
    Route::get('/mypage', [UserController::class, 'profile'])->name('user.profile');
    Route::get('/mypage/profile', [UserController::class, 'editProfile'])->name('user.edit-profile');
    Route::post('/mypage/profile', [UserController::class, 'updateProfile'])->name('user.update-profile');
    Route::post('/mypage/profile/image', [UserController::class, 'uploadProfileImage'])->name('user.profile.image');

    // 検索・API系
    Route::get('/users/search', [UserController::class, 'search'])->name('users.search');
    Route::get('/api/user/liked-items', [UserController::class, 'getLikedItems'])->name('api.user.liked');
    Route::get('/api/user/purchase-history', [UserController::class, 'getPurchaseHistory'])->name('api.user.purchases');
    Route::get('/api/user/selling-items', [UserController::class, 'getSellingItems'])->name('api.user.selling');
});

// ===== 認証は必要だがメール認証は不要なルート =====
Route::middleware('auth')->group(function () {
    // ログアウト
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
});

// Stripe決済成功時のコールバック
Route::get('/purchase/{item_id}/success', [PurchaseController::class, 'success'])
    ->name('purchase.success')
    ->middleware('auth');

// Stripe決済キャンセル時のコールバック
Route::get('/purchase/{item_id}/cancel', [PurchaseController::class, 'cancel'])
    ->name('purchase.cancel')
    ->middleware('auth');
