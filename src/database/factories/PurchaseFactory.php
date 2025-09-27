<?php

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Purchase::class;

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
            'postal_code' => $this->faker->postcode(),
            'address' => $this->faker->address(),
            'building' => $this->faker->optional()->secondaryAddress(),
            'payment_method' => $this->faker->randomElement(['card', 'convenience', 'bank_transfer']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * カード支払いの購入データを生成
     */
    public function cardPayment(): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_method' => 'card',
        ]);
    }

    /**
     * コンビニ支払いの購入データを生成
     */
    public function conveniencePayment(): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_method' => 'convenience',
        ]);
    }

    /**
     * 銀行振込の購入データを生成
     */
    public function bankTransferPayment(): static
    {
        return $this->state(fn(array $attributes) => [
            'payment_method' => 'bank_transfer',
        ]);
    }

    /**
     * 建物名なしの購入データを生成
     */
    public function withoutBuilding(): static
    {
        return $this->state(fn(array $attributes) => [
            'building' => null,
        ]);
    }

    /**
     * 特定の郵便番号形式の購入データを生成
     */
    public function withPostalCode(string $postalCode): static
    {
        return $this->state(fn(array $attributes) => [
            'postal_code' => $postalCode,
        ]);
    }

    /**
     * 特定の住所の購入データを生成
     */
    public function withAddress(string $address): static
    {
        return $this->state(fn(array $attributes) => [
            'address' => $address,
        ]);
    }

    /**
     * 東京都の住所の購入データを生成
     */
    public function tokyoAddress(): static
    {
        return $this->state(fn(array $attributes) => [
            'postal_code' => '150-0001',
            'address' => '東京都渋谷区神宮前1-1-1',
            'building' => $this->faker->optional()->secondaryAddress(),
        ]);
    }

    /**
     * 大阪府の住所の購入データを生成
     */
    public function osakaAddress(): static
    {
        return $this->state(fn(array $attributes) => [
            'postal_code' => '530-0001',
            'address' => '大阪府大阪市北区梅田1-1-1',
            'building' => $this->faker->optional()->secondaryAddress(),
        ]);
    }

    /**
     * 過去の日付の購入データを生成
     */
    public function pastPurchase(int $daysAgo = 30): static
    {
        return $this->state(fn(array $attributes) => [
            'created_at' => now()->subDays($daysAgo),
            'updated_at' => now()->subDays($daysAgo),
        ]);
    }

    /**
     * 最近の購入データを生成
     */
    public function recentPurchase(): static
    {
        return $this->state(fn(array $attributes) => [
            'created_at' => now()->subHours($this->faker->numberBetween(1, 24)),
            'updated_at' => now()->subHours($this->faker->numberBetween(1, 24)),
        ]);
    }
}
