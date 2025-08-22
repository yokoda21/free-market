<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // シーダー実行順序（外部キー制約を考慮）
        $this->call([
            // 1. マスタデータ（依存関係なし）
            CategoriesTableSeeder::class,
            ConditionsTableSeeder::class,
            
            // 2. ユーザー関連（プロフィール含む）
            UsersTableSeeder::class,
            
            // 3. 商品データ（ユーザー・コンディションに依存）
            ItemsTableSeeder::class,
            
            // 4. 関連データ（商品・ユーザーに依存）
            ItemCategoriesTableSeeder::class,
            LikesTableSeeder::class,
            CommentsTableSeeder::class,
        ]);
    }
}
