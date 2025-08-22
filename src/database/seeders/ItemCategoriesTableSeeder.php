<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ItemCategory;

class ItemCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 商品とカテゴリの紐付け
        // カテゴリID: 1=ファッション, 2=家電・スマホ・カメラ, 3=インテリア・住まい・小物, 4=コスメ・香水・美容, 5=その他
        $itemCategories = [
            ['item_id' => 1, 'category_id' => 1], // 腕時計 → ファッション
            ['item_id' => 2, 'category_id' => 2], // HDD → 家電・スマホ・カメラ
            ['item_id' => 3, 'category_id' => 5], // 玉ねぎ3束 → その他
            ['item_id' => 4, 'category_id' => 1], // 革靴 → ファッション
            ['item_id' => 5, 'category_id' => 2], // ノートPC → 家電・スマホ・カメラ
            ['item_id' => 6, 'category_id' => 2], // マイク → 家電・スマホ・カメラ
            ['item_id' => 7, 'category_id' => 1], // ショルダーバッグ → ファッション
            ['item_id' => 8, 'category_id' => 3], // タンブラー → インテリア・住まい・小物
            ['item_id' => 9, 'category_id' => 3], // コーヒーミル → インテリア・住まい・小物
            ['item_id' => 10, 'category_id' => 4], // メイクセット → コスメ・香水・美容
        ];

        foreach ($itemCategories as $itemCategory) {
            ItemCategory::create($itemCategory);
        }
    }
}
