<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'user_id' => 1,
                'name' => 'Armani 高級腕時計',
                'brand' => 'Armani',
                'description' => '状態の良いArmaniの腕時計です。\n数回使用しましたが、目立った傷や汚れはありません。\nビジネスシーンでもカジュアルでも使える洗練されたデザインです。',
                'price' => 15000,
                'img_url' => 'images/items/Armani+Mens+Clock.jpg',
                'condition_id' => 3,
                'is_sold' => false,
            ],
            [
                'user_id' => 1,
                'name' => 'HDD ハードディスク 2TB',
                'brand' => null,
                'description' => '外付けHDD 2TBです。\n使用期間は約1年程度。\n動作確認済みで問題なく使用できます。',
                'price' => 8000,
                'img_url' => 'images/items/HDD+Hard+Disk.jpg',
                'condition_id' => 3,
                'is_sold' => true,
            ],
            [
                'user_id' => 2,
                'name' => '本革ビジネスシューズ',
                'brand' => null,
                'description' => '本革を使用した高品質なビジネスシューズです。\nサイズ：26.0cm\n\n数回着用しましたが、サイズが合わなかったため出品します。',
                'price' => 12000,
                'img_url' => 'images/items/Leather+Shoes+Product+Photo.jpg',
                'condition_id' => 2,
                'is_sold' => false,
            ],
            [
                'user_id' => 2,
                'name' => 'MacBook Pro 13インチ',
                'brand' => 'Apple',
                'description' => 'MacBook Pro 2020年モデル。\nメモリ16GB、SSD512GB。\n使用感はありますが、動作は快適です。',
                'price' => 98000,
                'img_url' => 'images/items/Macbook+Pro.jpg',
                'condition_id' => 4,
                'is_sold' => false,
            ],
            [
                'user_id' => 3,
                'name' => 'ワイヤレスマイク',
                'brand' => null,
                'description' => '高音質ワイヤレスマイクです。\n配信や録音に最適。\n付属品完備。',
                'price' => 5500,
                'img_url' => 'images/items/Microphone+Black.jpg',
                'condition_id' => 3,
                'is_sold' => false,
            ],
        ];

        foreach ($items as $item) {
            $itemId = DB::table('items')->insertGetId(array_merge($item, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            // カテゴリー紐付け
            $categoryMap = [
                1 => [5, 12], // Armani時計: メンズ、アクセサリー
                2 => [2], // HDD: 家電
                3 => [5], // 靴: メンズ
                4 => [2], // MacBook: 家電
                5 => [2], // マイク: 家電
            ];

            if (isset($categoryMap[$itemId])) {
                foreach ($categoryMap[$itemId] as $categoryId) {
                    DB::table('item_category')->insert([
                        'item_id' => $itemId,
                        'category_id' => $categoryId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
