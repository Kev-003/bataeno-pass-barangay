<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Barangay;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\House>
 */
class HouseFactory extends Factory
{
    public function definition(): array
    {
        $barangay = Barangay::factory()->create(); /// We create it to get the name

        return [
            'barangay_id' => $barangay->id,
            'housing_unit' => 'Unit ' . fake()->buildingNumber(),
            'street' => fake()->streetName(),
            'subdivision' => fake()->optional()->word() . ' Ville',
            'barangay' => $barangay->name,
        ];
    }
}
