<?php

namespace Database\Factories;


use App\Models\Condition;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConditionFactory extends Factory
{

    protected $model = Condition::class;

    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['良好', '目立った傷や汚れなし', 'やや傷や汚れあり', '状態が悪い']),
        ];
    }
}
