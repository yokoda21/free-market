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

        return view('items.index', compact('items'));
    }

    /**
     * 商品詳細表示
     * FN017: 商品詳細情報取得
     * FN018: いいね機能
     * 
     * @param Item $item
     * @return \Illuminate\View\View
     */
    public function show(Item $item)
    {
        // 関連データを事前ロード
        $item->load([
            'user.profile',
            'condition',
            'categories',
            'comments.user.profile',
            'likes'
        ]);

        // いいね数とログインユーザーのいいね状態
        $likesCount = $item->likes->count();
        $isLiked = false;
        if (Auth::check()) {
            $isLiked = $item->isLikedBy(Auth::id());
        }

        // コメント数
        $commentsCount = $item->comments->count();

        // 購入情報（販売済みの場合）
        $purchase = $item->purchase;

        return view('items.show', compact(
            'item',
            'likesCount',
            'isLiked',
            'commentsCount',
            'purchase'
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
        // 未認証の場合はログイン画面にリダイレクト
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $validated = $request->validated();

        // 画像アップロード（Laravelのstorageディレクトリに保存）
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('items', 'public');
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

        // カテゴリ関連付け（複数選択可能）
        $item->categories()->attach($validated['category_ids']);

        return redirect()
            ->route('items.show', $item)
            ->with('success', '商品を出品しました。');
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
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ログインが必要です。'
                ], 401);
            }
            return redirect()->route('login');
        }

        $result = Like::toggle(Auth::id(), $item->id);

        // いいね数を再取得
        $likesCount = $item->fresh()->likes()->count();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'action' => $result['action'],
                'likes_count' => $likesCount,
                'is_liked' => $result['action'] === 'added'
            ]);
        }

        return redirect()->back();
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
