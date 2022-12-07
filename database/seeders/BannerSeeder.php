<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Banners;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $banner = [
            [
                "name" => 'adamjee',
                "banner_img" => 'adamjee.png'
            ],
            [
                "name" => 'asia',
                'banner_img' => 'asia.png'
            ],
            [
                'name' => 'askari',
                'banner_img' => 'askari.png'
            ],
            [
                'name' => 'efu',
                'banner_img' => 'efu.png'
            ],
            [
                'name' => 'igi_insurence',
                'banner_img' => 'igi_insurence.png'
            ],
            [
                'name' => 'jubliee',
                'banner_img' => 'jubliee.png'
            ],
            [
                'name' => 'state_life',
                'banner_img' => 'state_life.png'
            ],
            [
                'name' => 'uic',
                'banner_img' => 'uic.png'
            ]
        ];
        Banners::insert($banner);
    }
}
