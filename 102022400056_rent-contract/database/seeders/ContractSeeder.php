<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ContractSeeder extends Seeder
{
    public function run(): void
    {
        // $tenants = Tenant::all();

        // if ($tenants->isEmpty()) {
        //     $tenants = Tenant::factory()->count(10)->create();
        // }

        // foreach ($tenants as $tenant) {
        //     Contract::factory()->count(3)->create(['tenant_id' => $tenant->id]);
        // }

        // // Print sample UUIDs untuk testing di Swagger
        // $this->command->info('');
        // $this->command->info('=== Sample UUIDs untuk Testing Swagger ===');

        // Tenant::select('id', 'name', 'email')->limit(3)->get()
        //     ->each(fn ($t) => $this->command->info("Tenant  → {$t->id}  ({$t->name})"));

        // Contract::select('id', 'tenant_id', 'listing_id', 'status')->limit(3)->get()
        //     ->each(fn ($c) => $this->command->info("Contract → {$c->id}  [tenant: {$c->tenant_id}]  listing: {$c->listing_id}  status: {$c->status}"));

        // $this->command->info('==========================================');
        // $this->command->info('');
        Contract::create([
            'tenant_id' => 1,
            'listing_id' => 1,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Contract::create([
            'tenant_id' => 2,
            'listing_id' => 2,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Contract::create([
            'tenant_id' => 3,
            'listing_id' => 3,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}