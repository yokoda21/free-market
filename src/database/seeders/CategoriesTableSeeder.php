<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            ['name' => 'ファッション'],
            ['name' => '家電・スマホ・カメラ'],
            ['name' => 'インテリア・住まい・小物'],
            ['name' => 'コスメ・香水・美容'],
            ['name' => 'その他'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
