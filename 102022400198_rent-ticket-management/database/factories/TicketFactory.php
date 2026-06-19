<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'listing_id' => $this->faker->numberBetween(1, 5),
            'contract_id' => $this->faker->numberBetween(10, 15),
            'tenant_name' => $this->faker->name(),
            'description' => $this->faker->randomElement([
                'Pipa wastafel bocor sehingga air merembes ke lantai dapur.',
                'AC di kamar mati total dan tidak bisa dinyalakan.',
                'Saklar lampu ruang tengah konslet dan mengeluarkan percikan api.'
            ]),
            'status' => 'pending',
        ];
    }
}