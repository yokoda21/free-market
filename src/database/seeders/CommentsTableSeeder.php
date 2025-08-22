<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;

class CommentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // テストコメントデータ
        $comments = [
            // 腕時計への質問
            [
                'user_id' => 2, // 購入者花子
                'item_id' => 1, // 腕時計
                'comment' => 'この腕時計の購入時期はいつ頃でしょうか？'
            ],
            [
                'user_id' => 1, // 出品者太郎
                'item_id' => 1, // 腕時計
                'comment' => '昨年購入したものです。使用回数は数回程度です。'
            ],
            
            // HDDへの質問
            [
                'user_id' => 3, // テスト次郎
                'item_id' => 2, // HDD
                'comment' => '容量はどのくらいでしょうか？'
            ],
            [
                'user_id' => 2, // 購入者花子
                'item_id' => 2, // HDD
                'comment' => '1TBです。動作確認済みです。'
            ],
            
            // ノートPCへの質問
            [
                'user_id' => 1, // 出品者太郎
                'item_id' => 5, // ノートPC
                'comment' => 'スペックの詳細を教えてください。'
            ],
            [
                'user_id' => 2, // 購入者花子
                'item_id' => 5, // ノートPC
                'comment' => 'Core i7、メモリ16GB、SSD512GBです。'
            ],
            
            // ショルダーバッグへの質問
            [
                'user_id' => 3, // テスト次郎
                'item_id' => 7, // ショルダーバッグ
                'comment' => 'サイズ感はどの程度でしょうか？'
            ],
            
            // コーヒーミルへの質問
            [
                'user_id' => 2, // 購入者花子
                'item_id' => 9, // コーヒーミル
                'comment' => '使用感はいかがですか？'
            ],
            [
                'user_id' => 3, // テスト次郎
                'item_id' => 9, // コーヒーミル
                'comment' => '購入してから3ヶ月程度使用しました。とても使いやすいです。'
            ],
            
            // メイクセットへの質問
            [
                'user_id' => 2, // 購入者花子
                'item_id' => 10, // メイクセット
                'comment' => 'セット内容を詳しく教えてください。'
            ],
        ];

        foreach ($comments as $comment) {
            Comment::create($comment);
        }
    }
}
