<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 案件シート要件:
        // - ユーザー1（出品者太郎）: CO01〜CO05を出品
        // - ユーザー2（購入者花子）: CO06〜CO10を出品
        // - ユーザー3（テスト次郎）: 何も紐づけない
        $items = [
            // CO01〜CO05: 出品者太郎（user_id = 1）
            [
                'user_id' => 1, // 出品者太郎
                'name' => '腕時計',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'price' => 15000,
                'brand' => 'Rolax',
                'condition_id' => 1, // 良好
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg',
                'is_sold' => false,
            ],
            [
                'user_id' => 1, // 出品者太郎
                'name' => 'HDD',
                'description' => '高速で信頼性の高いハードディスク',
                'price' => 5000,
                'brand' => '西芝',
                'condition_id' => 2, // 目立った傷や汚れなし
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg',
                'is_sold' => false,
            ],
            [
                'user_id' => 1, // 出品者太郎
                'name' => '玉ねぎ3束',
                'description' => '新鮮な玉ねぎ3束のセット',
                'price' => 300,
                'brand' => null,
                'condition_id' => 3, // やや傷や汚れあり
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg',
                'is_sold' => false,
            ],
            [
                'user_id' => 1, // 出品者太郎
                'name' => '革靴',
                'description' => 'クラシックなデザインの革靴',
                'price' => 4000,
                'brand' => null,
                'condition_id' => 4, // 状態が悪い
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg',
                'is_sold' => false,
            ],
            [
                'user_id' => 1, // 出品者太郎
                'name' => 'ノートPC',
                'description' => '高性能なノートパソコン',
                'price' => 45000,
                'brand' => null,
                'condition_id' => 1, // 良好
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg',
                'is_sold' => false,
            ],
            // CO06〜CO10: 購入者花子（user_id = 2）
            [
                'user_id' => 2, // 購入者花子
                'name' => 'マイク',
                'description' => '高音質のレコーディング用マイク',
                'price' => 8000,
                'brand' => null,
                'condition_id' => 2, // 目立った傷や汚れなし
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg',
                'is_sold' => false,
            ],
            [
                'user_id' => 2, // 購入者花子
                'name' => 'ショルダーバッグ',
                'description' => 'おしゃれなショルダーバッグ',
                'price' => 3500,
                'brand' => null,
                'condition_id' => 3, // やや傷や汚れあり
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg',
                'is_sold' => false,
            ],
            [
                'user_id' => 2, // 購入者花子
                'name' => 'タンブラー',
                'description' => '使いやすいタンブラー',
                'price' => 500,
                'brand' => null,
                'condition_id' => 4, // 状態が悪い
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg',
                'is_sold' => false,
            ],
            [
                'user_id' => 2, // 購入者花子
                'name' => 'コーヒーミル',
                'description' => '手動のコーヒーミル',
                'price' => 4000,
                'brand' => 'Starbacks',
                'condition_id' => 1, // 良好
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg',
                'is_sold' => false,
            ],
            [
                'user_id' => 2, // 購入者花子
                'name' => 'メイクセット',
                'description' => '便利なメイクアップセット',
                'price' => 2500,
                'brand' => null,
                'condition_id' => 2, // 目立った傷や汚れなし
                'image_url' => 'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg',
                'is_sold' => false,
            ],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}
