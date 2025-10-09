<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected $condition;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        // テスト用の条件とカテゴリを作成
        $this->condition = Condition::factory()->create(['name' => '新品・未使用']);
        $this->category = Category::factory()->create(['name' => 'ファッション']);
    }

    /**
     * 購入画面が表示できることを確認
     */
    public function test_purchase_page_can_be_displayed()
    {
        // ユーザーと商品を作成
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'condition_id' => $this->condition->id
        ]);

        // 購入者としてログイン
        $response = $this->actingAs($buyer)->get("/purchase/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('商品購入');
        $response->assertSee($item->name);
        $response->assertSee('¥' . number_format($item->price));
    }

    /**
     * 未ログインユーザーは購入画面にアクセスできないことを確認
     */
    public function test_guest_cannot_access_purchase_page()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $user->id,
            'condition_id' => $this->condition->id
        ]);

        $response = $this->get("/purchase/{$item->id}");

        $response->assertRedirect('/login');
    }

    /**
     * 自分の商品は購入できないことを確認
     */
    public function test_user_cannot_purchase_own_item()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $user->id,
            'condition_id' => $this->condition->id
        ]);

        $response = $this->actingAs($user)->get("/purchase/{$item->id}");

        // 実際のリダイレクト先に合わせて修正
        $response->assertRedirect("/item/{$item->id}");
        $response->assertSessionHas('error', '自分の商品は購入できません。');
    }

    /**
     * 売却済み商品は購入できないことを確認
     */
    public function test_user_cannot_purchase_sold_item()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'condition_id' => $this->condition->id,
            'is_sold' => true
        ]);

        $response = $this->actingAs($buyer)->get("/purchase/{$item->id}");

        // 実際のリダイレクト先とメッセージに合わせて修正
        $response->assertRedirect("/item/{$item->id}");
        $response->assertSessionHas('error', 'この商品は既に購入されています。');
    }

    /**
     * 正常な購入処理ができることを確認
     */
    public function test_user_can_purchase_item_with_valid_data()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        // 購入者のプロフィールを作成
        Profile::factory()->create([
            'user_id' => $buyer->id,
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区神南1-1-1',
            'building' => 'テストビル101'
        ]);

        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'condition_id' => $this->condition->id
        ]);

        $purchaseData = [
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区神南1-1-1',
            'building' => 'テストビル101',
            'payment_method' => 'convenience'
        ];

        $response = $this->actingAs($buyer)->post("/purchase/{$item->id}", $purchaseData);

        $response->assertRedirect('/');

        // データベースに購入情報が保存されているか確認
        $this->assertDatabaseHas('purchases', [
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区神南1-1-1',
            'building' => 'テストビル101',
            'payment_method' => 'convenience'
        ]);

        // 商品が売却済みになっているか確認
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'is_sold' => true
        ]);
    }


    /**
     * 支払い方法は購入に必須であることを確認
     */
    public function test_payment_method_is_required_for_purchase()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'condition_id' => $this->condition->id
        ]);

        $purchaseData = [
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区神南1-1-1',
            'building' => 'テストビル101',
            'payment_method' => ''
        ];

        $response = $this->actingAs($buyer)->post("/purchase/{$item->id}", $purchaseData);

        $response->assertSessionHasErrors('payment_method');
        $this->assertDatabaseMissing('purchases', [
            'user_id' => $buyer->id,
            'item_id' => $item->id
        ]);
    }
    /**
     * 支払い方法の選択が購入画面に反映されていることを確認
     */
    public function test_payment_method_is_reflected_on_purchase_page()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        Profile::factory()->create([
            'user_id' => $buyer->id,
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区',
        ]);

        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'condition_id' => $this->condition->id
        ]);

        $response = $this->actingAs($buyer)->get("/purchase/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('支払い方法');
        $response->assertSee('選択してください');
    }

    /**
     * 住所変更機能が正常に動作することを確認
     */
    public function test_user_can_change_shipping_address()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        // 購入者のプロフィールを作成
        Profile::factory()->create([
            'user_id' => $buyer->id,
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区神南1-1-1',
            'building' => 'テストビル101'
        ]);

        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'condition_id' => $this->condition->id
        ]);

        // 住所変更画面にアクセス
        $response = $this->actingAs($buyer)->get("/purchase/address/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('住所の変更');

        // 新しい住所を設定
        $newAddressData = [
            'postal_code' => '456-7890',
            'address' => '大阪府大阪市北区梅田1-1-1',
            'building' => '梅田ビル201'
        ];

        $response = $this->actingAs($buyer)->post("/purchase/address/{$item->id}", $newAddressData);

        $response->assertRedirect("/purchase/{$item->id}");
    }

    /**
     * 購入履歴が正しく表示されることを確認
     */
    public function test_user_can_view_purchase_history()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        // 複数の商品を購入
        $item1 = Item::factory()->create([
            'user_id' => $seller->id,
            'name' => '商品1',
            'condition_id' => $this->condition->id
        ]);
        $item2 = Item::factory()->create([
            'user_id' => $seller->id,
            'name' => '商品2',
            'condition_id' => $this->condition->id
        ]);

        Purchase::factory()->create([
            'user_id' => $buyer->id,
            'item_id' => $item1->id,
            'payment_method' => 'convenience'
        ]);

        Purchase::factory()->create([
            'user_id' => $buyer->id,
            'item_id' => $item2->id,
            'payment_method' => 'convenience'
        ]);

        // マイページの購入商品一覧にアクセス
        $response = $this->actingAs($buyer)->get('/mypage?page=buy');

        $response->assertStatus(200);
        $response->assertSee('商品1');
        $response->assertSee('商品2');
    }

    /**
     * 同じ商品を複数回購入できないことを確認
     */
    public function test_user_cannot_purchase_same_item_twice()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'condition_id' => $this->condition->id
        ]);

        // 最初の購入
        Purchase::factory()->create([
            'user_id' => $buyer->id,
            'item_id' => $item->id
        ]);

        // 商品を売却済みに設定
        $item->update(['is_sold' => true]);

        $purchaseData = [
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区神南1-1-1',
            'building' => 'テストビル101',
            'payment_method' => 'convenience'
        ];

        // 同じ商品の再購入を試行
        $response = $this->actingAs($buyer)->post("/purchase/{$item->id}", $purchaseData);

        $response->assertRedirect("/item/{$item->id}");
        $response->assertSessionHas('error', 'この商品は既に購入されています。');
    }
}
