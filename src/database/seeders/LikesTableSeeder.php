<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Like;

class LikesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // テストユーザーのいいねデータ
        $likes = [
            // 出品者太郎(ID:1)のいいね
            ['user_id' => 1, 'item_id' => 2], // HDD
            ['user_id' => 1, 'item_id' => 5], // ノートPC
            ['user_id' => 1, 'item_id' => 6], // マイク
            
            // 購入者花子(ID:2)のいいね
            ['user_id' => 2, 'item_id' => 1], // 腕時計
            ['user_id' => 2, 'item_id' => 4], // 革靴
            ['user_id' => 2, 'item_id' => 7], // ショルダーバッグ
            ['user_id' => 2, 'item_id' => 9], // コーヒーミル
            
            // テスト次郎(ID:3)のいいね
            ['user_id' => 3, 'item_id' => 1], // 腕時計
            ['user_id' => 3, 'item_id' => 8], // タンブラー
            ['user_id' => 3, 'item_id' => 10], // メイクセット
        ];

        foreach ($likes as $like) {
            Like::create($like);
        }
    }
}
