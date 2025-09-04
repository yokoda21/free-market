<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Like;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
//use App\Http\Requests\ExhibitionRequest;

/**
 * 商品コントローラー
 * フリマアプリの商品機能を管理
 */
class ItemController extends Controller
{
    /**
     * 商品一覧表示（トップ画面）
     * FN014: 商品一覧取得
     * - 全商品を表示
     * - 商品画像、商品名を表示
     * - 購入済み商品は "Sold" と表示される
     * - 自分が出品した商品は表示されない
     * - 未認証ユーザーにも表示
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Item::with(['user.profile', 'condition', 'categories'])
            ->available();

        // 自分が出品した商品は表示されない（ログイン時のみ）
        if (Auth::check()) {
            $query->where('user_id', '!=', Auth::id());
        }

        // FN016: 商品検索機能
        // ヘッダー内の検索欄で「商品名」の部分一致検索
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where('name', 'LIKE', "%{$searchTerm}%");
        }

        // タブ切り替え（マイリスト）
        if ($request->input('tab') === 'mylist') {
            if (Auth::check()) {
                // FN015: マイリスト一覧取得
                // いいねした商品だけが表示される
                $query = Auth::user()->likedItems()
                    ->with(['user.profile', 'condition', 'categories']);

                // 検索状態がマイリストでも保持される
                if ($request->filled('search')) {
                    $searchTerm = $request->input('search');
                    $query->where('name', 'LIKE', "%{$searchTerm}%");
                }
            } else {
                // 未認証の場合は何も表示されない
                $query = Item::whereRaw('1 = 0'); // 空のクエリ
            }
        }

        $items = $query->latest()->paginate(20)->withQueryString();
        // 各商品にユーザーのいいね状態を追加
        if (Auth::check()) {
            $items->getCollection()->transform(function ($item) {
                $item->is_liked_by_user = $item->likes->where('user_id', Auth::id())->count() > 0;
                return $item;
            });
        }

        return view('items.index', compact('items'));
    }

    /**
     * 商品詳細表示
     * PG05: /item/{item_id}
     * 
     * @param Item $item
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // 商品情報を取得（リレーション含む）
        $item = Item::with([
            'user',           // 出品者情報
            'categories',     // カテゴリー情報
            'condition',      // 商品状態
            'comments.user',  // コメントと投稿者情報
            'likes',          // いいね情報
            'purchase'        // 購入情報
        ])->findOrFail($id);

        // 現在のユーザーがいいねしているかチェック
        $isLiked = false;
        $likesCount = $item->likes->count();

        if (Auth::check()) {
            $isLiked = $item->likes->where('user_id', Auth::id())->count() > 0;
        }

        // 購入済みかチェック
        $isPurchased = $item->purchase !== null;

        // 自分の商品かチェック
        $isOwnItem = Auth::check() && $item->user_id === Auth::id();

        // コメント数を取得
        $commentsCount = $item->comments->count();

        return view('items.show', compact(
            'item',
            'isLiked',
            'likesCount',
            'isPurchased',
            'isOwnItem',
            'commentsCount'
        ));
    }

    /**
     * 商品出品フォーム表示
     * 認証必須
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        // 未認証の場合はログイン画面にリダイレクト
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $categories = Category::orderBy('name')->get();
        $conditions = Condition::orderBy('id')->get();
        \Log::info('データ取得完了', [
            'categories_count' => $categories->count(),
            'conditions_count' => $conditions->count()
        ]);



        return view('items.create', compact('categories', 'conditions'));
    }

    /**
     * 商品出品処理
     * FN028: 出品商品情報登録機能
     * FN029: 出品商品画像アップロード機能
     * 
     * @param \App\Http\Requests\ExhibitionRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(\App\Http\Requests\ExhibitionRequest $request)
    {
        \Log::info('=== ExhibitionRequest使用版 ===');
        \Log::info('認証状況:', ['authenticated' => Auth::check(), 'user_id' => Auth::id()]);

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        try {
            $validated = $request->validated();
            \Log::info('ExhibitionRequestバリデーション成功:', $validated);

            // 画像アップロード処理の詳細ログ
            $imagePath = null;
            if ($request->hasFile('image')) {
                \Log::info('=== 画像アップロード開始 ===');
                \Log::info('画像ファイル情報:', [
                    'name' => $request->file('image')->getClientOriginalName(),
                    'size' => $request->file('image')->getSize(),
                    'mime' => $request->file('image')->getMimeType(),
                    'tmp_path' => $request->file('image')->getRealPath()
                ]);

                try {
                    $imagePath = $request->file('image')->store('items', 'public');
                    \Log::info('画像保存成功: ' . $imagePath);

                    // 実際にファイルが存在するか確認
                    $fullPath = storage_path('app/public/' . $imagePath);
                    \Log::info('保存先フルパス: ' . $fullPath);
                    \Log::info('ファイル存在確認: ' . (file_exists($fullPath) ? 'YES' : 'NO'));
                } catch (\Exception $e) {
                    \Log::error('画像保存エラー: ' . $e->getMessage());
                    \Log::error('画像保存エラー詳細: ' . $e->getTraceAsString());
                    throw $e;
                }
            } else {
                \Log::warning('画像ファイルが受信されていません');
            }

            // 商品作成
            $item = Item::create([
                'user_id' => Auth::id(),
                'name' => $validated['name'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'brand' => $validated['brand'] ?? null,
                'condition_id' => $validated['condition_id'],
                'image_url' => $imagePath,
                'is_sold' => false,
            ]);

            \Log::info('商品作成完了:', ['item_id' => $item->id, 'image_url' => $imagePath]);

            // カテゴリー関連付け
            if (!empty($validated['category_ids'])) {
                $item->categories()->attach($validated['category_ids']);
                \Log::info('カテゴリー関連付け完了:', ['category_ids' => $validated['category_ids']]);
            }

            return redirect()
                ->route('items.index')
                ->with('success', '商品を出品しました');
        } catch (\Exception $e) {
            \Log::error('商品出品処理エラー:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withInput()->with('error', 'エラー: ' . $e->getMessage());
        }
    }
    /**
     * いいね機能
     * FN018: いいね機能
     * - いいねアイコンを押下することによって、いいねした商品として登録
     * - 再度いいねアイコンを押下することによって、いいねを解除
     *
     * @param Item $item
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function toggleLike(Item $item)
    {
        // ログインユーザーのみ
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'ログインが必要です。'
            ], 401);
        }

        $userId = Auth::id();

        $existingLike = Like::where('user_id', $userId)
            ->where('item_id', $item->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $action = 'removed';
        } else {
            Like::create([
                'user_id' => $userId,
                'item_id' => $item->id
            ]);
            $action = 'added';
        }

        $likesCount = $item->fresh()->likes()->count();

        return response()->json([
            'success' => true,
            'action' => $action,
            'likes_count' => $likesCount,
            'is_liked' => $action === 'added'
        ]);
    }





    /**
     * コメント投稿
     * FN020: コメント送信機能
     * - ログインユーザーのみがコメントを送信
     * - FormRequestを使用したバリデーション
     * 
     * @param \App\Http\Requests\CommentRequest $request
     * @param Item $item
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeComment(\App\Http\Requests\CommentRequest $request, Item $item)
    {
        // ログインユーザーのみ
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $validated = $request->validated();

        Comment::create([
            'user_id' => Auth::id(),
            'item_id' => $item->id,
            'comment' => $validated['comment'],
        ]);

        return redirect()
            ->route('items.show', $item)
            ->with('success', 'コメントを投稿しました。');
    }

    /**
     * 商品購入画面表示
     * FN019: 購入手続き動線
     * FN021: 購入前商品情報取得機能
     *
     * @param Item $item
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function purchase(Item $item)
    {
        // 認証必須
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 既に購入済みの場合
        if ($item->is_sold) {
            return redirect()
                ->route('items.show', $item)
                ->with('error', 'この商品は既に購入されています。');
        }

        // 自分の商品は購入不可
        if ($item->user_id === Auth::id()) {
            return redirect()
                ->route('items.show', $item)
                ->with('error', '自分の商品は購入できません。');
        }

        // プロフィール情報から住所を初期値として取得
        $profile = Auth::user()->profile;

        return view('items.purchase', compact('item', 'profile'));
    }

    /**
     * 商品購入処理
     * FN022: 商品購入機能
     * FN023: 支払い方法選択機能
     * FN024: 配送先変更機能
     *
     * @param \App\Http\Requests\PurchaseRequest $request
     * @param Item $item
     * @return \Illuminate\Http\RedirectResponse
     */
    public function completePurchase(\App\Http\Requests\PurchaseRequest $request, Item $item)
    {
        // 認証必須
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 既に購入済みの場合
        if ($item->is_sold) {
            return redirect()
                ->route('items.show', $item)
                ->with('error', 'この商品は既に購入されています。');
        }

        // 自分の商品は購入不可
        if ($item->user_id === Auth::id()) {
            return redirect()
                ->route('items.show', $item)
                ->with('error', '自分の商品は購入できません。');
        }

        $validated = $request->validated();

        // 購入処理
        $item->purchases()->create([
            'user_id' => Auth::id(),
            'postal_code' => $validated['postal_code'],
            'address' => $validated['address'],
            'building' => $validated['building'] ?? null,
            'payment_method' => $validated['payment_method'],
        ]);

        // 商品を売り切れにする
        $item->update(['is_sold' => true]);

        // 購入完了後は商品一覧画面に遷移
        return redirect()
            ->route('items.index')
            ->with('success', '商品を購入しました。');
    }

    /**
     * 配送先住所変更画面表示
     * FN024: 配送先変更機能
     *
     * @param Item $item
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function editAddress(Item $item)
    {
        // 認証必須
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // 既に購入済みの場合
        if ($item->is_sold) {
            return redirect()
                ->route('items.show', $item)
                ->with('error', 'この商品は既に購入されています。');
        }

        $profile = Auth::user()->profile;

        return view('items.edit-address', compact('item', 'profile'));
    }

    /**
     * 商品検索API
     * FN016: 商品検索機能（Ajax対応）
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1|max:100',
        ]);

        $query = $request->input('q');

        $items = Item::where('name', 'LIKE', "%{$query}%")
            ->available()
            ->when(Auth::check(), function ($q) {
                return $q->where('user_id', '!=', Auth::id());
            })
            ->with(['user', 'condition'])
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'formatted_price' => $item->formatted_price,
                    'image_url' => $item->image_url ? asset('storage/' . $item->image_url) : null,
                    'condition' => $item->condition->name,
                    'is_sold' => $item->is_sold,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }
}
