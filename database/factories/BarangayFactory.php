<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Municipality;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Barangay>
 */
class BarangayFactory extends Factory
{
    public function definition(): array
    {
        return [
            'municipality_id' => Municipality::factory(),
            'name' => 'Brgy. ' . fake()->streetName(),
        ];
    }
}
