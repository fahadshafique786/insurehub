<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vehicle;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $vehicle = [
            [
                'name' => 'BMW',
                'class_id' => 1
            ],
            [
                'name' => 'Audi',
                'class_id' => 1
            ],
            [
                'name' => 'Honda',
                'class_id' => 1
            ],
            [
                'name' => 'Toyota',
                'class_id' => 1
            ],
            [
                'name' => 'Suzuki',
                'class_id' => 1
            ]
        ];
        Vehicle::insert($vehicle);
    }
}
