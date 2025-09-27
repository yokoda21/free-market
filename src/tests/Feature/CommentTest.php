<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use App\Models\Comment;
use App\Models\Category;
use App\Models\Condition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CommentTest extends TestCase
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
     * 認証済みユーザーがコメントを投稿できることを確認
     */
    public function test_authenticated_user_can_post_comment()
    {
        $itemOwner = User::factory()->create();
        $commenter = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'condition_id' => $this->condition->id
        ]);

        $commentData = [
            'comment' => 'この商品の状態はいかがですか？'
        ];

        $response = $this->actingAs($commenter)->post("/item/{$item->id}/comment", $commentData);

        $response->assertRedirect("/item/{$item->id}");
        $response->assertSessionHas('success', 'コメントを投稿しました。');

        // データベースにコメントが保存されているか確認
        $this->assertDatabaseHas('comments', [
            'user_id' => $commenter->id,
            'item_id' => $item->id,
            'comment' => 'この商品の状態はいかがですか？'
        ]);
    }

    /**
     * 未ログインユーザーはコメントを投稿できないことを確認
     */
    public function test_guest_cannot_post_comment()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $user->id,
            'condition_id' => $this->condition->id
        ]);

        $commentData = [
            'comment' => 'コメントテスト'
        ];

        $response = $this->post("/item/{$item->id}/comment", $commentData);

        $response->assertRedirect('/login');

        // データベースにコメントが保存されていないことを確認
        $this->assertDatabaseMissing('comments', [
            'item_id' => $item->id,
            'comment' => 'コメントテスト'
        ]);
    }

    /**
     * コメント内容が必須であることを確認
     */
    public function test_comment_content_is_required()
    {
        $itemOwner = User::factory()->create();
        $commenter = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'condition_id' => $this->condition->id
        ]);

        $commentData = [
            'comment' => ''
        ];

        $response = $this->actingAs($commenter)->post("/item/{$item->id}/comment", $commentData);

        $response->assertSessionHasErrors('comment');

        // データベースにコメントが保存されていないことを確認
        $this->assertDatabaseMissing('comments', [
            'user_id' => $commenter->id,
            'item_id' => $item->id
        ]);
    }

    /**
     * 長すぎるコメントはバリデーションエラーになることを確認
     */
    public function test_comment_content_has_max_length()
    {
        $itemOwner = User::factory()->create();
        $commenter = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'condition_id' => $this->condition->id
        ]);

        $commentData = [
            'comment' => str_repeat('あ', 256) // 255文字を超える
        ];

        $response = $this->actingAs($commenter)->post("/item/{$item->id}/comment", $commentData);

        $response->assertSessionHasErrors('comment');

        // データベースにコメントが保存されていないことを確認
        $this->assertDatabaseMissing('comments', [
            'user_id' => $commenter->id,
            'item_id' => $item->id
        ]);
    }

    /**
     * 商品詳細ページでコメントが表示されることを確認
     */
    public function test_comments_are_displayed_on_item_detail_page()
    {
        $itemOwner = User::factory()->create(['name' => '出品者']);
        $commenter1 = User::factory()->create(['name' => 'コメンター1']);
        $commenter2 = User::factory()->create(['name' => 'コメンター2']);

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'condition_id' => $this->condition->id
        ]);

        // 複数のコメントを作成
        Comment::create([
            'user_id' => $commenter1->id,
            'item_id' => $item->id,
            'comment' => '商品の状態はどうですか？'
        ]);

        Comment::create([
            'user_id' => $commenter2->id,
            'item_id' => $item->id,
            'comment' => '購入を検討しています。'
        ]);

        $response = $this->get("/item/{$item->id}");

        $response->assertStatus(200);
        $response->assertSee('商品の状態はどうですか？');
        $response->assertSee('購入を検討しています。');
        $response->assertSee('コメンター1');
        $response->assertSee('コメンター2');
    }

    /**
     * 自分の商品にもコメントできることを確認
     */
    public function test_user_can_comment_on_own_item()
    {
        $user = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $user->id,
            'condition_id' => $this->condition->id
        ]);

        $commentData = [
            'comment' => '追加情報です。'
        ];

        $response = $this->actingAs($user)->post("/item/{$item->id}/comment", $commentData);

        $response->assertRedirect("/item/{$item->id}");
        $response->assertSessionHas('success', 'コメントを投稿しました。');

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'comment' => '追加情報です。'
        ]);
    }

    /**
     * 売却済み商品にもコメントできることを確認
     */
    public function test_user_can_comment_on_sold_item()
    {
        $itemOwner = User::factory()->create();
        $commenter = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'condition_id' => $this->condition->id,
            'is_sold' => true
        ]);

        $commentData = [
            'comment' => '売却後の質問です。'
        ];

        $response = $this->actingAs($commenter)->post("/item/{$item->id}/comment", $commentData);

        $response->assertRedirect("/item/{$item->id}");
        $response->assertSessionHas('success', 'コメントを投稿しました。');

        $this->assertDatabaseHas('comments', [
            'user_id' => $commenter->id,
            'item_id' => $item->id,
            'comment' => '売却後の質問です。'
        ]);
    }

    /**
     * コメントが時系列順に表示されることを確認（新しい順）
     */
    public function test_comments_are_displayed_in_chronological_order()
    {
        $itemOwner = User::factory()->create();
        $commenter = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'condition_id' => $this->condition->id
        ]);

        // 時間をずらしてコメントを作成
        $comment1 = Comment::create([
            'user_id' => $commenter->id,
            'item_id' => $item->id,
            'comment' => '最初のコメント',
            'created_at' => now()->subHours(2)
        ]);

        $comment2 = Comment::create([
            'user_id' => $commenter->id,
            'item_id' => $item->id,
            'comment' => '2番目のコメント',
            'created_at' => now()->subHour()
        ]);

        $comment3 = Comment::create([
            'user_id' => $commenter->id,
            'item_id' => $item->id,
            'comment' => '最新のコメント',
            'created_at' => now()
        ]);

        $response = $this->get("/item/{$item->id}");

        $response->assertStatus(200);

        // コメントが新しい順（降順）で表示されることを確認
        $content = $response->getContent();
        $firstPos = strpos($content, '最初のコメント');
        $secondPos = strpos($content, '2番目のコメント');
        $thirdPos = strpos($content, '最新のコメント');

        // 新しい順なので最新→2番目→最初の順で表示される
        $this->assertLessThan($secondPos, $thirdPos);
        $this->assertLessThan($firstPos, $secondPos);
    }

    /**
     * 存在しない商品にコメントしようとした場合404エラーになることを確認
     */
    public function test_commenting_on_nonexistent_item_returns_404()
    {
        $user = User::factory()->create();

        $commentData = [
            'comment' => 'コメント'
        ];

        $response = $this->actingAs($user)->post("/item/999999/comment", $commentData);

        $response->assertStatus(404);
    }

    /**
     * コメント投稿時にXSS攻撃が防がれることを確認
     */
    public function test_comment_prevents_xss_attacks()
    {
        $itemOwner = User::factory()->create();
        $commenter = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'condition_id' => $this->condition->id
        ]);

        $commentData = [
            'comment' => '<script>alert("XSS")</script>悪意のあるコメント'
        ];

        $response = $this->actingAs($commenter)->post("/item/{$item->id}/comment", $commentData);

        $response->assertRedirect("/item/{$item->id}");

        // データベースには元のコメントが保存される
        $this->assertDatabaseHas('comments', [
            'user_id' => $commenter->id,
            'item_id' => $item->id,
            'comment' => '<script>alert("XSS")</script>悪意のあるコメント'
        ]);

        // 表示時にエスケープされることを確認
        $response = $this->get("/item/{$item->id}");
        $response->assertDontSee('<script>alert("XSS")</script>', false);
        // 実際のエスケープ形式に合わせて修正
        $response->assertSee('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', false);
    }

    /**
     * 大量のコメントがある場合でもパフォーマンスが良いことを確認
     */
    public function test_item_page_loads_efficiently_with_many_comments()
    {
        $itemOwner = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'condition_id' => $this->condition->id
        ]);

        // 50個のコメントを作成
        for ($i = 1; $i <= 50; $i++) {
            Comment::create([
                'user_id' => $itemOwner->id,
                'item_id' => $item->id,
                'comment' => "テストコメント{$i}"
            ]);
        }

        $startTime = microtime(true);
        $response = $this->get("/item/{$item->id}");
        $endTime = microtime(true);

        $response->assertStatus(200);

        // レスポンス時間が妥当であることを確認（2秒以内）
        $this->assertLessThan(2.0, $endTime - $startTime);
    }

    /**
     * コメント数が正しくカウントされることを確認
     */
    public function test_comment_count_is_correct()
    {
        $itemOwner = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'condition_id' => $this->condition->id
        ]);

        // 3個のコメントを作成
        for ($i = 1; $i <= 3; $i++) {
            Comment::create([
                'user_id' => $itemOwner->id,
                'item_id' => $item->id,
                'comment' => "コメント{$i}"
            ]);
        }

        $response = $this->get("/item/{$item->id}");

        $response->assertStatus(200);
        // コメント数の表示を確認
        $response->assertSee('コメント (3)');
    }

    /**
     * 空のコメント（空白のみ）が投稿できないことを確認
     */
    public function test_whitespace_only_comment_is_invalid()
    {
        $itemOwner = User::factory()->create();
        $commenter = User::factory()->create();

        $item = Item::factory()->create([
            'user_id' => $itemOwner->id,
            'condition_id' => $this->condition->id
        ]);

        $commentData = [
            'comment' => '   　　　   ' // 半角・全角スペースのみ
        ];

        $response = $this->actingAs($commenter)->post("/item/{$item->id}/comment", $commentData);

        $response->assertSessionHasErrors('comment');

        $this->assertDatabaseMissing('comments', [
            'user_id' => $commenter->id,
            'item_id' => $item->id
        ]);
    }
}
