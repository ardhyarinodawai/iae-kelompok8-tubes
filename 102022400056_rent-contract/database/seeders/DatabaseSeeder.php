<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create or find the test user to avoid unique constraint on re-seed
        // User::updateOrCreate(
        //     ['email' => 'warga01@ktp.iae.id'],
        //     ['name' => 'Warga 01', 'password' => bcrypt('password_lokal'), 'role' => 'admin']
        // );

        // User::updateOrCreate(
        //     ['email' => 'warga02@ktp.iae.id'],
        //     ['name' => 'Warga 02', 'password' => bcrypt('password_lokal'), 'role' => 'user']
        // );

        

        // Seed tenants and contracts
        $this->call([
            TenantSeeder::class,
            ContractSeeder::class,
        ]);
    }
}
