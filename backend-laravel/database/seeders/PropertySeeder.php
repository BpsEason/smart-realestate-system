<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        DB::table('properties')->insert([
            [
                'address' => '台北市大安區新生南路一段',
                'area' => 35.50,
                'price' => 3500.00,
                'description' => '捷運站旁，生活機能極佳，學區房。採光良好，社區管理完善。',
                'image_url' => 'https://placehold.co/800x600/E0F2F7/4299E1?text=建案一',
                'latitude' => 25.03396,
                'longitude' => 121.56447,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'address' => '台北市信義區忠孝東路五段',
                'area' => 50.25,
                'price' => 5800.00,
                'description' => '近台北101，視野開闊，豪宅首選。高樓層景觀，交通便利。',
                'image_url' => 'https://placehold.co/800x600/F0F8FF/4682B4?text=建案二',
                'latitude' => 25.03362,
                'longitude' => 121.56543,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'address' => '台北市中山區南京東路三段',
                'area' => 22.80,
                'price' => 2300.00,
                'description' => '小資首選，交通便利，獨立套房。新裝修，拎包入住。',
                'image_url' => 'https://placehold.co/800x600/FFFACD/DAA520?text=建案三',
                'latitude' => 25.05069,
                'longitude' => 121.54359,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'address' => '新北市板橋區文化路一段',
                'area' => 45.00,
                'price' => 2800.00,
                'description' => '生活機能完善，近百貨公司。公園旁，適合家庭居住。',
                'image_url' => 'https://placehold.co/800x600/E6E6FA/7B68EE?text=建案四',
                'latitude' => 25.01188,
                'longitude' => 121.46467,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'address' => '台北市士林區士林夜市旁',
                'area' => 28.10,
                'price' => 2950.00,
                'description' => '熱鬧商圈，生活機能便利，投資自住皆宜。',
                'image_url' => 'https://placehold.co/800x600/F5F5DC/8B4513?text=建案五',
                'latitude' => 25.08836,
                'longitude' => 121.52479,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'address' => '台北市文山區景美街',
                'area' => 30.00,
                'price' => 2500.00,
                'description' => '近景美夜市，生活機能便利，交通便捷。',
                'image_url' => 'https://placehold.co/800x600/DDA0DD/800080?text=建案六',
                'latitude' => 24.9958,
                'longitude' => 121.5385,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'address' => '新北市新莊區中正路',
                'area' => 40.00,
                'price' => 3200.00,
                'description' => '近捷運站，學區房，適合小家庭。',
                'image_url' => 'https://placehold.co/800x600/98FB98/006400?text=建案七',
                'latitude' => 25.0408,
                'longitude' => 121.4429,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'address' => '台北市南港區忠孝東路七段',
                'area' => 55.00,
                'price' => 6000.00,
                'description' => '高檔住宅區，環境清幽，視野極佳。',
                'image_url' => 'https://placehold.co/800x600/AFEEEE/008B8B?text=建案八',
                'latitude' => 25.0504,
                'longitude' => 121.6191,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'address' => '新北市淡水區學府路',
                'area' => 20.00,
                'price' => 1500.00,
                'description' => '近淡水大學，適合學生或單身人士。',
                'image_url' => 'https://placehold.co/800x600/F0E68C/B8860B?text=建案九',
                'latitude' => 25.1764,
                'longitude' => 121.4485,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'address' => '台北市大安區忠孝東路四段',
                'area' => 48.00,
                'price' => 5200.00,
                'description' => '精華地段，交通便利，商業繁榮。',
                'image_url' => 'https://placehold.co/800x600/ADD8E6/4682B4?text=建案十',
                'latitude' => 25.0425,
                'longitude' => 121.5542,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
