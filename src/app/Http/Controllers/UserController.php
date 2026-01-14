<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProfileRequest;
use App\Models\User;
use App\Models\Profile;
use App\Models\Item;
use App\Models\TradeMessage;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * 会員登録処理（メール認証対応）
     */
    public function register(RegisterRequest $request)
    {
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

        // ログイン処理
        Auth::login($user);

        // メール認証通知送信
        event(new Registered($user));

        // メール認証誘導画面へリダイレクト
        return redirect()->route('verification.notice');
    }

    /**
     * ログイン処理（メール認証チェック付き）
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        if (Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password']
        ])) {
            $request->session()->regenerate();

            // メール認証チェック
            if (!Auth::user()->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            // 認証済みの場合は商品一覧へ
            return redirect()->intended(route('items.index'))->with('success', 'ログインしました');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->onlyInput('email');
    }

    // ログアウト処理
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('items.index')->with('success', 'ログアウトしました');
    }

    // プロフィール表示    
    public function profile(Request $request)
    {
        $user = Auth::user();
        $page = $request->query('page', 'sell');

        if ($page === 'sell') {
            // 出品した商品（全て）
            $items = Item::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($page === 'buy') {
            // 購入した商品
            $items = Item::whereHas('purchase', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with('purchase')
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($page === 'trading') {
            // 取引中の商品（購入 + 出品）
            // 条件：取引中 OR 取引完了だが未評価

            // 1. 自分が購入した商品（取引中 OR 取引完了だが未評価）
            $purchasedItems = Item::whereHas('purchase', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where(function ($q) {
                        $q->where('is_completed', false)
                            ->orWhere(function ($q2) {
                                $q2->where('is_completed', true)
                                    ->where('buyer_evaluated', false);
                            });
                    });
            })->with(['purchase', 'likes', 'condition'])
                ->get();

            // 2. 自分が出品した商品（取引中 OR 取引完了だが未評価）
            $soldItems = Item::where('user_id', $user->id)
                ->where('is_sold', true)
                ->whereHas('purchase', function ($query) {
                    $query->where(function ($q) {
                        $q->where('is_completed', false)
                            ->orWhere(function ($q2) {
                                $q2->where('is_completed', true)
                                    ->where('seller_evaluated', false);
                            });
                    });
                })
                ->with(['purchase', 'likes', 'condition'])
                ->get();

            // 3. 両方をマージして最新メッセージ順にソート（FN004対応）
            $items = $purchasedItems->merge($soldItems)
                ->sortByDesc(function ($item) {
                    // Eager Loadingではなく、直接クエリで最新メッセージを取得
                    $latestMessage = \App\Models\TradeMessage::where('purchase_id', $item->purchase->id)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    return $latestMessage ? $latestMessage->created_at : $item->purchase->created_at;
                })
                ->values(); // コレクションのキーをリセット            
        } else {
            $items = Item::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
            $page = 'sell';
        }

        return view('user.profile', compact('user', 'items', 'page'));
    }

    public function editProfile()
    {
        $user = Auth::user();
        $profile = $user->profile;
        return view('user.edit-profile', compact('user', 'profile'));
    }

    public function updateProfile(ProfileRequest $request)
    {
        $user = Auth::user();
        $profile = $user->profile;
        $validated = $request->validated();

        if ($request->hasFile('profile_image')) {
            if ($profile->profile_image) {
                Storage::disk('public')->delete($profile->profile_image);
            }
            $imagePath = $request->file('profile_image')->store('profile_images', 'public');
            $validated['profile_image'] = $imagePath;
        }

        $user->update(['name' => $validated['name']]);

        $profile->update([
            'postal_code' => $validated['postal_code'],
            'address' => $validated['address'],
            'building' => $validated['building'] ?? '',
            'profile_image' => $validated['profile_image'] ?? $profile->profile_image,
        ]);

        return redirect()->route('user.profile')->with('success', 'プロフィールを更新しました');
    }
}
