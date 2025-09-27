<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase; //テストごとにDBをリセット

    /**
     * テストケース1: ログイン画面が表示される
     * 
     * @test
     */
    public function login_page_can_be_displayed()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('メールアドレス')
            ->assertSee('パスワード')
            ->assertSee('ログイン');
    }
    /**
     * テストケース2: 正しい認証情報でログインできる
     * 
     * @test
     */
    public function user_can_login_with_valid_credentials()
    {
        // Arrange: テスト用ユーザーを作成
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'), // パスワードをハッシュ化
        ]);

        // Act: ログインフォームに正しい情報を送信
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',  // 平文で送信
        ]);

        // Assert: ログインが成功している
        $this->assertAuthenticated(); // ユーザーがログイン状態か確認

        // Assert: ログイン後のページにリダイレクトされる
        $response->assertRedirect('/'); // または適切なリダイレクト先
    }
    /**
     * テストケース3: メールアドレスが未入力の場合バリデーションエラー
     * 
     * @test
     */
    public function email_is_required_for_login()
    {
        // Act: メールアドレス未入力でログイン送信
        $response = $this->post('/login', [
            'email' => '',  // 空文字
            'password' => 'password123',
        ]);
        // デバッグ：実際のリダイレクト先を確認
        $response->assertSessionHasErrors('email');
        $response->assertRedirect('/');  // トップページへのリダイレクト
        $this->assertGuest();
    }
    /**
     * テストケース4: パスワードが未入力の場合バリデーションエラー
     * 
     * @test
     */
    public function password_is_required_for_login()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',  // 空文字
        ]);

        $response->assertSessionHasErrors('password');
        $response->assertRedirect('/');
        $this->assertGuest();
    }
    /**
     * テストケース5: 不正な認証情報でログイン失敗
     * 
     * @test
     */
    public function login_fails_with_invalid_credentials()
    {
        // Arrange: 正しいユーザーを作成
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct_password'),
        ]);

        // Act: 間違ったパスワードでログイン試行
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong_password',
        ]);

        // Assert: ログインが失敗している
        $this->assertGuest();

        // Assert: バリデーションエラー（要件: 「ログイン情報が登録されていません」）
        $response->assertSessionHasErrors('email');
    }
}



