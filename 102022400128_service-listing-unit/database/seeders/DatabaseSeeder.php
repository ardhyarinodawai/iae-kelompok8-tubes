<?php

namespace Database\Seeders;

use App\Models\Listing;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Listing::updateOrCreate(
            [
                'unit_code' => '056',
                'unit_name' => 'Apartemen Tower A 056',
            ]
        );
        Listing::updateOrCreate(
            [
                'unit_code' => '128',
                'unit_name' => 'Apartemen Tower B 128',
            ]
        );
        Listing::updateOrCreate(
            [
                'unit_code' => '198',
                'unit_name' => 'Apartemen Tower C 198',
            ]
        );
    }
}
