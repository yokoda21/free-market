<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;
use App\Models\User;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テスト1: 会員登録後、認証メールが送信される
     */
    public function test_verification_email_is_sent_after_registration()
    {
        // 通知をモック（Mailableではなく）
        Notification::fake();

        // 会員登録
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // ユーザーが作成されたことを確認
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // 認証メール（通知）が送信されたことを確認
        $user = User::where('email', 'test@example.com')->first();
        Notification::assertSentTo(
            [$user],
            VerifyEmail::class
        );

        // メール認証画面にリダイレクトされることを確認
        $response->assertRedirect('/email/verify');
    }

    /**
     * テスト2: メール認証誘導画面が表示される
     */
    public function test_email_verification_screen_can_be_rendered()
    {
        // 未認証ユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // ログイン
        $response = $this->actingAs($user)->get('/email/verify');

        // メール認証画面が表示される
        $response->assertStatus(200);
    }

    /**
     * テスト3: メール認証を完了するとプロフィール設定画面に遷移する
     */
    public function test_email_can_be_verified()
    {
        // 未認証ユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 認証URL生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 認証URLにアクセス
        $response = $this->actingAs($user)->get($verificationUrl);

        // メール認証が完了したことを確認
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        // プロフィール設定画面にリダイレクト
        $response->assertRedirect('/mypage/profile');
    }

    /**
     * テスト4: 既に認証済みの場合は商品一覧ページにリダイレクト
     */
    public function test_already_verified_user_redirects_to_home()
    {
        // 認証済みユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // メール認証画面にアクセス
        $response = $this->actingAs($user)->get('/email/verify');

        // 商品一覧ページにリダイレクト
        $response->assertRedirect('/');
    }

    /**
     * テスト5: 認証メールの再送信ができる
     */
    public function test_verification_email_can_be_resent()
    {
        Notification::fake();

        // 未認証ユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 認証メールを再送信
        $response = $this->actingAs($user)->post('/email/verification-notification');

        // 通知が送信されたことを確認
        Notification::assertSentTo(
            [$user],
            VerifyEmail::class
        );

        // セッションにメッセージが保存される
        $response->assertSessionHas('status');
    }
}
