<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubClassess;


class SubClassessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subClass = [
            [
                'name' => 'Conventional Insurance',
                'class_id' => '1'
            ],
            [
                'name' => 'Takaful',
                'class_id' => '1'
            ],
            [
                'name' => 'Conventional Insurance',
                'class_id' => '2'
            ],
            [
                'name' => 'Takaful',
                'class_id' => '2'
            ],
            [
                'name' => 'Conventional Insurance',
                'class_id' => '4'
            ],
            [
                'name' => 'Takaful',
                'class_id' => '4'
            ] 
        ];
        SubClassess::insert($subClass);
    }
}
