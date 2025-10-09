<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LogoutTest extends TestCase
{
    use RefreshDatabase; //テストごとにDBをリセット

    /**
     * テストケース: ログアウトできる
     * 
     * @test
     */

    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
