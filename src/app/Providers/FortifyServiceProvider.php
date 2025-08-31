<?php

namespace App\Providers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Illuminate\Support\Facades\Validator;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // ログイン画面の表示設定
        Fortify::loginView(function () {
            return view('auth.login');
        });

        // 会員登録画面の表示設定
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // === カスタムバリデーション設定 ===

        // ログインバリデーションのカスタマイズ
        // LoginRequestは代わりにFormRequestMiddlewareで処理

        // 会員登録バリデーションのカスタマイズ
        Fortify::createUsersUsing(\App\Actions\Fortify\CreateNewUser::class);

        // レート制限設定
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(5)->by($email . $request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // 認証後のリダイレクト先設定
        $this->app->singleton(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            \App\Http\Responses\LoginResponse::class
        );

        $this->app->singleton(
            \Laravel\Fortify\Contracts\RegisterResponse::class,
            \App\Http\Responses\RegisterResponse::class
        );

        // パスワードリセット画面（将来的に使用）
        // Fortify::requestPasswordResetLinkView(function () {
        //     return view('auth.forgot-password');
        // });

        // Fortify::resetPasswordView(function ($request) {
        //     return view('auth.reset-password', ['request' => $request]);
        // });

        // メール認証画面（将来的に使用）
        // Fortify::verifyEmailView(function () {
        //     return view('auth.verify-email');
        // });

        // 2段階認証画面（将来的に使用）
        // Fortify::twoFactorChallengeView(function () {
        //     return view('auth.two-factor-challenge');
        // });

        // Fortify::confirmPasswordView(function () {
        //     return view('auth.confirm-password');
        // });
    }
}
