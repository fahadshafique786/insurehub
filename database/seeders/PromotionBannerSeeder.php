<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PromotionBanner;

class PromotionBannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $promotionBanner = [
            [
                "name" => 'promotion Banner',
                'promotion_banner' => 'promotion_banner.png'
            ]
        ];
        PromotionBanner::insert($promotionBanner);
    }
}
