<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => '山田太郎',
                'email' => 'yamada@example.com',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '田中花子',
                'email' => 'tanaka@example.com',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '佐藤次郎',
                'email' => 'sato@example.com',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            $userId = DB::table('users')->insertGetId($user);

            // プロフィール作成
            DB::table('profiles')->insert([
                'user_id' => $userId,
                'postal_code' => '123-4567',
                'address' => '東京都渋谷区神宮前1-2-3',
                'building' => 'サンプルマンション101',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
