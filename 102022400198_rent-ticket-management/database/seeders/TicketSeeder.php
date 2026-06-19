<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ticket::create([
            'listing_id' => 1,
            'contract_id' => 1,
            'tenant_name' => 'Akhdan',
            'tenant_email' => 'akhdan@example.com',
            'description' => 'Pintu WC tidak bisa di tutup.',
        ]);
        Ticket::create([
            'listing_id' => 2,
            'contract_id' => 2,
            'tenant_name' => 'Rafsanzani',
            'tenant_email' => 'rafsanzani@example.com',
            'description' => 'Atap Bocor.',
        ]);
        Ticket::create([
            'listing_id' => 3,
            'contract_id' => 3,
            'tenant_name' => 'Dawai',
            'tenant_email' => 'dawai@example.com',
            'description' => 'Air WC tidak mengalir.',
        ]);

        // You can add more Ticket::create([]) entries here if needed
    }
}
