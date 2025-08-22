<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

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
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(5)->by($email . $request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // カスタム認証ビューの設定
        Fortify::loginView(function () {
            return view('auth.login');
        });

        Fortify::registerView(function () {
            return view('auth.register');
        });

        // パスワードリセット機能を使用する場合（あとからオプション）
        // Fortify::requestPasswordResetLinkView(function () {
        //     return view('auth.forgot-password');
        // });

        // Fortify::resetPasswordView(function ($request) {
        //     return view('auth.reset-password', ['request' => $request]);
        // });

        // メール認証を使用する場合（あとからオプション）
        // Fortify::verifyEmailView(function () {
        //     return view('auth.verify-email');
        // });

        // 2段階認証を使用する場合（あとからオプション）
        // Fortify::twoFactorChallengeView(function () {
        //     return view('auth.two-factor-challenge');
        // });

        // Fortify::confirmPasswordView(function () {
        //     return view('auth.confirm-password');
        // });
    }
}
