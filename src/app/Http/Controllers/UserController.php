<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProfileRequest;
use App\Models\User;
use App\Models\Profile;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * 会員登録処理
     * RegisterRequestでバリデーション、自動プロフィール作成
     */
    public function register(RegisterRequest $request)
    {
        // バリデーション済みデータの取得
        $validated = $request->validated();

        // ユーザー作成
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // プロフィール初期データ作成
        Profile::create([
            'user_id' => $user->id,
            'postal_code' => '',
            'address' => '',
            'building' => '',
            'profile_image' => null,
        ]);

        // 自動ログイン
        Auth::login($user);

        // 商品一覧画面にリダイレクト
        return redirect()->route('items.index')->with('success', '会員登録が完了しました');
    }

    /**
     * ログイン処理
     * LoginRequestでバリデーション
     */
    public function login(LoginRequest $request)
    {
        // バリデーション済みデータの取得
        $validated = $request->validated();

        // ログイン試行
        if (Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password']
        ])) {
            // セッション再生成（セキュリティ対策）
            $request->session()->regenerate();

            // 商品一覧画面にリダイレクト
            return redirect()->intended(route('items.index'))->with('success', 'ログインしました');
        }

        // ログイン失敗時（日本語メッセージ）
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->onlyInput('email');
    }

    /**
     * ログアウト処理
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // セッション無効化
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 商品一覧画面にリダイレクト
        return redirect()->route('items.index')->with('success', 'ログアウトしました');
    }

    /**
     * マイページ表示
     * PG09: /mypage (出品商品がデフォルト)
     * PG11: /mypage?page=buy (購入した商品一覧)
     * PG12: /mypage?page=sell (出品した商品一覧)
     */
    public function profile(Request $request)
    {
        $user = Auth::user();
        $page = $request->query('page', 'sell'); // 模擬案件仕様：pageパラメータ、デフォルトは出品商品

        if ($page === 'sell') {
            // PG12: 出品した商品を取得
            $items = Item::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($page === 'buy') {
            // PG11: 購入した商品を取得
            $items = Item::whereHas('purchase', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with('purchase') // 購入情報も一緒に取得
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // 不正なパラメータの場合は出品商品を表示
            $items = Item::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
            $page = 'sell';
        }

        // ビューに渡すデータ：user, items, page（tabではなくpage）
        return view('user.profile', compact('user', 'items', 'page'));
    }

    /**
     * プロフィール編集画面表示
     */
    public function editProfile()
    {
        $user = Auth::user();
        $profile = $user->profile;

        return view('user.edit-profile', compact('user', 'profile'));
    }

    /**
     * プロフィール更新処理
     */
    public function updateProfile(ProfileRequest $request)
    {
        $user = Auth::user();
        $profile = $user->profile;
        $validated = $request->validated();

        // プロフィール画像の処理
        if ($request->hasFile('profile_image')) {
            // 既存画像の削除
            if ($profile->profile_image) {
                Storage::disk('public')->delete($profile->profile_image);
            }

            // 新しい画像の保存
            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
            $validated['profile_image'] = $imagePath;
        }

        // ユーザー情報の更新
        $user->update([
            'name' => $validated['name']
        ]);

        // プロフィール情報の更新
        $profile->update([
            'postal_code' => $validated['postal_code'],
            'address' => $validated['address'],
            'building' => $validated['building'] ?? '',
            'profile_image' => $validated['profile_image'] ?? $profile->profile_image,
        ]);

        return redirect()->route('user.profile')->with('success', 'プロフィールを更新しました');
    }

    /**
     * いいねした商品一覧の取得（商品一覧画面のマイリストタブ用）
     * ItemControllerから呼び出される場合もあるため、パブリックメソッドとして提供
     */
    public function getLikedItems()
    {
        $user = Auth::user();

        return Item::whereHas('likes', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('is_sold', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * ユーザーの購入履歴取得
     */
    public function getPurchaseHistory()
    {
        $user = Auth::user();

        return Item::whereHas('purchase', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with('purchase')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * ユーザーの出品商品取得
     */
    public function getSellingItems()
    {
        $user = Auth::user();

        return Item::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * ユーザーがコメントした商品一覧取得
     */
    public function getCommentedItems()
    {
        $user = Auth::user();

        return Item::whereHas('comments', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->distinct()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * プロフィール画像のアップロード処理（Ajax対応）
     */
    public function uploadProfileImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png|max:2048'
        ]);

        $user = Auth::user();
        $profile = $user->profile;

        // 既存画像の削除
        if ($profile->profile_image) {
            Storage::disk('public')->delete($profile->profile_image);
        }

        // 新しい画像の保存
        $imagePath = $request->file('profile_image')->store('profile_images', 'public');

        // プロフィール更新
        $profile->update([
            'profile_image' => $imagePath
        ]);

        return response()->json([
            'success' => true,
            'image_url' => Storage::url($imagePath),
            'message' => 'プロフィール画像を更新しました'
        ]);
    }

    /**
     * ユーザー検索機能（管理機能として）
     */
    public function search(Request $request)
    {
        $keyword = $request->query('keyword');

        if (empty($keyword)) {
            return redirect()->back()->with('error', '検索キーワードを入力してください');
        }

        $users = User::where('name', 'LIKE', "%{$keyword}%")
            ->orWhere('email', 'LIKE', "%{$keyword}%")
            ->with('profile')
            ->paginate(20);

        return view('user.search', compact('users', 'keyword'));
    }
}
