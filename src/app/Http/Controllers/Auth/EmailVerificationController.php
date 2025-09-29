<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    /**
     * メール認証誘導画面表示
     */
    public function notice()
    {
        // 既に認証済みの場合は商品一覧へリダイレクト
        if (auth()->user()->hasVerifiedEmail()) {
            return redirect()->route('items.index');
        }

        return view('auth.verify-email');
    }

    /**
     * メール認証完了処理（プロフィール編集画面へ遷移）
     */
    public function complete(Request $request)
    {
        // メール認証を完了
        if (!$request->user()->hasVerifiedEmail()) {
            $request->user()->markEmailAsVerified();
        }

        // プロフィール編集画面へリダイレクト
        return redirect()->route('user.edit-profile')
            ->with('message', 'メールアドレスの認証が完了しました。プロフィール情報を入力してください。');
    }

    /**
     * 認証メール再送信
     */
    public function resend(Request $request)
    {
        // 既に認証済みの場合
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('items.index');
        }

        // 認証メール送信
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', '認証メールを再送信しました。');
    }
}
