<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the tenant seeds.
     */
    public function run(): void
    {
        Tenant::create([
            'tenant_name' => 'Akhdan',
            'tenant_email' => 'akhdan@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Tenant::create([
            'tenant_name' => 'Rafsanzani',
            'tenant_email' => 'rafsanzani@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Tenant::create([
            'tenant_name' => 'Dawai',
            'tenant_email' => 'dawai@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
