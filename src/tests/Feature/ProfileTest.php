<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Storageのfakeを毎回設定
        Storage::fake('public');
    }

    /**
     * マイページが表示できることを確認
     */
    public function test_user_can_view_mypage()
    {
        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/mypage');

        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    /**
     * プロフィール編集画面が表示できることを確認
     */
    public function test_user_can_view_profile_edit_page()
    {
        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/mypage/profile');

        $response->assertStatus(200);
        $response->assertSee('プロフィール設定');
    }

    /**
     * プロフィール更新テスト（POST方式）
     */
    public function test_user_can_update_profile()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        $profileData = [
            'name' => '更新太郎',
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区',
            'building' => 'テストビル101'
        ];

        $response = $this->actingAs($user)
            ->post('/mypage/profile', $profileData);

        $response->assertRedirect('/mypage');

        // データベース確認
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => '更新太郎'
        ]);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区',
            'building' => 'テストビル101'
        ]);
    }

    /**
     * プロフィール画像アップロードテスト（GD拡張対応）
     */
    public function test_user_can_upload_profile_image()
    {
        // GD拡張が利用できない場合はテストをスキップ
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('GD拡張が利用できないため、画像テストをスキップします');
        }

        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        $file = UploadedFile::fake()->image('profile.jpg');

        $response = $this->actingAs($user)
            ->post('/mypage/profile', [
                'name' => $user->name,
                'postal_code' => '123-4567',
                'address' => '東京都渋谷区',
                'profile_image' => $file
            ]);

        $response->assertRedirect('/mypage');

        // ファイルが保存されたことを確認
        $this->assertTrue(
            collect(Storage::disk('public')->files('profile_images'))
                ->contains(function ($file) {
                    return str_contains($file, '.jpg');
                })
        );
    }

    /**
     * 未ログインアクセス制限テスト
     */
    public function test_guest_cannot_access_profile_pages()
    {
        $response = $this->get('/mypage');
        $response->assertRedirect('/login');

        $response = $this->get('/mypage/profile');
        $response->assertRedirect('/login');
    }

    /**
     * プロフィール更新バリデーションテスト
     */
    public function test_profile_update_validation_works()
    {
        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);

        // 名前が空の場合
        $response = $this->actingAs($user)
            ->post('/mypage/profile', [
                'name' => '',
                'postal_code' => '123-4567',
                'address' => '東京都渋谷区'
            ]);

        $response->assertSessionHasErrors('name');

        // 郵便番号の形式が間違っている場合
        $response = $this->actingAs($user)
            ->post('/mypage/profile', [
                'name' => 'テスト太郎',
                'postal_code' => '1234567', // ハイフンなし
                'address' => '東京都渋谷区'
            ]);

        $response->assertSessionHasErrors('postal_code');

        // 住所が空の場合
        $response = $this->actingAs($user)
            ->post('/mypage/profile', [
                'name' => 'テスト太郎',
                'postal_code' => '123-4567',
                'address' => ''
            ]);

        $response->assertSessionHasErrors('address');
    }

    /**
     * 建物名は任意項目であることを確認
     */
    public function test_building_field_is_optional()
    {
        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post('/mypage/profile', [
                'name' => 'テスト太郎',
                'postal_code' => '123-4567',
                'address' => '東京都渋谷区'
                // building は送信しない
            ]);

        $response->assertRedirect('/mypage');
        $response->assertSessionHasNoErrors();
    }

    /**
     * プロフィールの表示情報が正しいことを確認
     */
    public function test_profile_displays_correct_information()
    {
        $user = User::factory()->create(['name' => '表示テスト太郎']);
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'postal_code' => '100-0001',
            'address' => '東京都千代田区',
            'building' => 'テストマンション101'
        ]);

        $response = $this->actingAs($user)->get('/mypage/profile');

        $response->assertStatus(200);
        $response->assertSee('表示テスト太郎');
        $response->assertSee('100-0001');
        $response->assertSee('東京都千代田区');
        $response->assertSee('テストマンション101');
    }
}
