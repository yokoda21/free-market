<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use App\Models\Condition;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{

    protected $model = Item::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->text(200),
            'price' => $this->faker->numberBetween(100, 50000),
            'brand' => $this->faker->company,
            'condition_id' => Condition::factory(),
            'image_url' => 'test.jpg',
            'is_sold' => false,
        ];
    }
}
