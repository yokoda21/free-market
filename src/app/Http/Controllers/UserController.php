<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ProfileRequest;
use App\Models\User;
use App\Models\Profile;
use App\Models\Item;
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
            $items = Item::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($page === 'buy') {
            $items = Item::whereHas('purchase', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->with('purchase')
                ->orderBy('created_at', 'desc')
                ->get();
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


    public function getLikedItems() {}
    public function getPurchaseHistory() {}
    public function getSellingItems() {}
    public function getCommentedItems() {}
    public function uploadProfileImage(Request $request) {}
    public function search(Request $request) {}
}
