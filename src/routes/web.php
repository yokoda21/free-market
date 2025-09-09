<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
|
*/

// ===== 認証ルート（Laravel Fortify提供・カスタマイズ済み） =====
// Fortifyが自動的に以下のルートを提供：
// GET /login - ログイン画面表示
// POST /login - ログイン処理（LoginRequestバリデーション適用済み）
// GET /register - 会員登録画面表示  
// POST /register - 会員登録処理（RegisterRequestバリデーション適用済み）
// POST /logout - ログアウト処理

// ===== パブリックルート（認証不要） =====

// 商品一覧(PG01)
Route::get('/', [ItemController::class, 'index'])->name('items.index');

// 商品検索機能
Route::get('/search', [ItemController::class, 'index'])->name('items.search');

// 商品詳細表示(PG05)
Route::get('/item/{item}', [ItemController::class, 'show'])->name('items.show');

// ===== 認証必須ルート =====(PG09, PG10, PG11, PG12)
Route::middleware(['auth'])->group(function () {

    // ===== 商品関連機能 =====

    // 商品出品(PG08)
    Route::get('/sell', [ItemController::class, 'create'])->name('items.create');
    Route::post('/sell', [ItemController::class, 'store'])->name('items.store');

    
    

    // いいね機能（Ajax対応）
    Route::post('/items/{item}/like', [ItemController::class, 'toggleLike'])
        ->middleware('auth')
        ->name('items.like');


    // コメント機能
    Route::post('/item/{item}/comment', [ItemController::class, 'storeComment'])->name('items.comment');

    // 商品検索（認証ユーザー用の追加機能）
    Route::get('/items/search', [ItemController::class, 'search'])->name('items.search.advanced');

    // ===== 購入関連機能 =====

    // 商品購入画面(PG06)
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'show'])->name('purchase.show');

    // 住所変更画面
    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])->name('purchase.address');
    Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');

    // 購入処理
    Route::post('/purchase/{item_id}', [PurchaseController::class, 'store'])->name('purchase.store');

    // ===== ユーザー・プロフィール関連機能 =====

    // マイページ
    Route::get('/mypage', [UserController::class, 'profile'])->name('user.profile');

    // プロフィール編集
    Route::get('/mypage/profile', [UserController::class, 'editProfile'])->name('user.edit-profile');
    Route::post('/mypage/profile', [UserController::class, 'updateProfile'])->name('user.update-profile');

    // プロフィール画像アップロード（Ajax対応）
    Route::post('/mypage/profile/image', [UserController::class, 'uploadProfileImage'])->name('user.profile.image');

    // ===== 追加機能（管理・検索系） =====

    // ユーザー検索（管理機能）
    Route::get('/users/search', [UserController::class, 'search'])->name('users.search');

    // いいねした商品一覧API（内部利用）
    Route::get('/api/user/liked-items', [UserController::class, 'getLikedItems'])->name('api.user.liked');

    // 購入履歴API（内部利用）
    Route::get('/api/user/purchase-history', [UserController::class, 'getPurchaseHistory'])->name('api.user.purchases');

    // 出品商品一覧API（内部利用）
    Route::get('/api/user/selling-items', [UserController::class, 'getSellingItems'])->name('api.user.selling');
});

// ===== 管理者ルート（将来的拡張用） =====
//Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
// 管理者用機能は将来実装予定
// Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
//});

// ===== Fortifyカスタム認証ルート（FormRequest適用） =====
// 日本語バリデーションメッセージを表示するため、カスタムルートを使用

// 会員登録処理（RegisterRequest適用）(PG03, PG04)
Route::post('/register', [UserController::class, 'register'])->name('register.custom');

// ログイン処理（LoginRequest適用）
Route::post('/login', [UserController::class, 'login'])->name('login.custom');

// ===== エラーページルート =====
/*Route::fallback(function () {
    return view('errors.404');
});動作確認のため2025年9月1日一時コメントアウト
*/

/*
|--------------------------------------------------------------------------
| ルート設計メモ
|--------------------------------------------------------------------------
|
| 【基本設計書準拠】
| - 商品一覧: /（?tab=mylist対応）
| - 商品詳細: /item/{item_id}
| - 商品出品: /sell（GET・POST）
| - 商品購入: /purchase/{item_id}
| - 住所変更: /purchase/address/{item_id}
| - マイページ: /mypage（?tab=sell|buy対応）
| - プロフィール編集: /mypage/profile
| - ログイン: /login（GET・POST）
| - 会員登録: /register（GET・POST）
| - ログアウト: /logout（POST）
|
| 【認証設計】
| - Laravel Fortify使用
| - ミドルウェア: auth（認証必須）
| - ゲストアクセス: 商品一覧・詳細のみ
|
| 【Ajax対応】
| - いいね機能: POST /items/{item}/like
| - プロフィール画像: POST /mypage/profile/image
| - API系: /api/プレフィックス
|
| 【RESTful設計】
| - GET: 表示画面
| - POST: 作成・更新処理
| - リソースベースのURL設計
|
*/