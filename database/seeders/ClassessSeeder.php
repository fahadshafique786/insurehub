<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Classes;

class ClassessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $Classes = [
            [
                "name" => 'Moter'
               
            ],
            [
                "name" => 'Health'
                
            ],
            [
                'name' => 'Home'
                
            ],
            [
                'name' => 'Fly'
            ],
            [
                'name' => 'PA'
                
            ]
        ];
        Classes::insert($Classes);
    }
}
