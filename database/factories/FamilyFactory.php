<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Family>
 */
class FamilyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'family_name' => fake()->lastName(),
        ];
    }
}
