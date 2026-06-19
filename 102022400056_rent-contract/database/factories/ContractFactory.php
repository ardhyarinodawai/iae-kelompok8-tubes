<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContractFactory extends Factory
{
    protected $model = Contract::class;

    public function definition()
    {
        $start = $this->faker->dateTimeBetween('-1 years', 'now')->format('Y-m-d');
        $end = $this->faker->dateTimeBetween($start, '+2 years')->format('Y-m-d');
        $status = $this->faker->randomElement(['DRAFT', 'ACTIVE', 'EXPIRED', 'TERMINATED']);
        $isActive = $status === 'ACTIVE';

        return [
            'id' => (string) Str::uuid(),
            'tenant_id' => Tenant::factory(),
            'listing_id' => (string) Str::uuid(),
            'start_date' => $start,
            'end_date' => $end,
            'is_active' => $isActive,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
