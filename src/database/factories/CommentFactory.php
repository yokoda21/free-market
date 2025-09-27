<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'item_id' => Item::factory(),
            'comment' => $this->faker->realText(200),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * 短いコメントを生成
     */
    public function short(): static
    {
        return $this->state(fn(array $attributes) => [
            'comment' => $this->faker->sentence(),
        ]);
    }

    /**
     * 長いコメントを生成
     */
    public function long(): static
    {
        return $this->state(fn(array $attributes) => [
            'comment' => $this->faker->realText(500),
        ]);
    }

    /**
     * 質問形式のコメントを生成
     */
    public function question(): static
    {
        return $this->state(fn(array $attributes) => [
            'comment' => $this->faker->sentence() . 'でしょうか？',
        ]);
    }

    /**
     * 感謝のコメントを生成
     */
    public function thanks(): static
    {
        return $this->state(fn(array $attributes) => [
            'comment' => 'ありがとうございます！',
        ]);
    }

    /**
     * 商品への問い合わせコメントを生成
     */
    public function inquiry(): static
    {
        $inquiries = [
            'こちらの商品の状態はいかがですか？',
            '値下げは可能でしょうか？',
            'まだ購入可能でしょうか？',
            'サイズを教えていただけますか？',
            '送料はいくらくらいでしょうか？'
        ];

        return $this->state(fn(array $attributes) => [
            'comment' => $this->faker->randomElement($inquiries),
        ]);
    }

    /**
     * 特定の日時のコメントを生成
     */
    public function createdAt(\DateTime $date): static
    {
        return $this->state(fn(array $attributes) => [
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }

    /**
     * 過去のコメントを生成
     */
    public function past(int $daysAgo = 7): static
    {
        $date = now()->subDays($daysAgo);
        return $this->state(fn(array $attributes) => [
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }

    /**
     * 最近のコメントを生成
     */
    public function recent(): static
    {
        $date = now()->subHours($this->faker->numberBetween(1, 24));
        return $this->state(fn(array $attributes) => [
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }
}
