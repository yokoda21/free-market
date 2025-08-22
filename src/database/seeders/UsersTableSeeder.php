<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => '出品者太郎',
                'email' => 'seller1@example.com',
                'password' => Hash::make('password'),
                'profile' => [
                    'postal_code' => '150-0001',
                    'address' => '東京都渋谷区神宮前1-1-1',
                    'building' => 'テストマンション101',
                ]
            ],
            [
                'name' => '購入者花子',
                'email' => 'buyer1@example.com', 
                'password' => Hash::make('password'),
                'profile' => [
                    'postal_code' => '530-0001',
                    'address' => '大阪府大阪市北区梅田1-1-1',
                    'building' => 'オフィスビル202',
                ]
            ],
            [
                'name' => 'テスト次郎',
                'email' => 'test1@example.com',
                'password' => Hash::make('password'),
                'profile' => [
                    'postal_code' => '231-0001',
                    'address' => '神奈川県横浜市中区新港町1-1-1',
                    'building' => '',
                ]
            ],
        ];

        foreach ($users as $userData) {
            $profileData = $userData['profile'];
            unset($userData['profile']);
            
            $user = User::create($userData);
            
            $profileData['user_id'] = $user->id;
            Profile::create($profileData);
        }
    }
}
