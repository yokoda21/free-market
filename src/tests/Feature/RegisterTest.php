<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase; //テストごとにDBをリセット


    /**
     * 会員登録ページが表示されるかのテスト
     */
    public function test_register_page_can_be_displayed()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    /**
     * 正常な情報で会員登録ができるかのテスト
     */
    public function test_user_can_register_with_valid_data()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // リダイレクトが発生することを確認
        $this->assertTrue($response->isRedirection());

        // データベースにユーザーが作成されていることを確認
        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com'
        ]);
    }

    /**
     * 名前が未入力の場合のバリデーションテスト
     */
    public function test_name_is_required_for_registration()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // バリデーションエラーがあることを確認
        $response->assertSessionHasErrors('name');

        // ユーザーが作成されていないことを確認
        $this->assertDatabaseCount('users', 0);
    }

    /**
     * メールアドレスが未入力の場合のバリデーションテスト
     */
    public function test_email_is_required_for_registration()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 0);
    }

    /**
     * パスワードが未入力の場合のバリデーションテスト
     */
    public function test_password_is_required_for_registration()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 0);
    }
}
