<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\House;
use App\Models\HouseholdMemberProfile;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Household>
 */
class HouseholdFactory extends Factory
{
    public function definition(): array
    {
        return [
            'house_id' => House::factory(),
            // household_head_id will be set after creating members usually, but we can make it nullable or create one
            // 'household_head_id' => ... 
            'ownership' => fake()->randomElement(['Owned', 'Rented', 'Living with Relatives']),
            'monthly_utility_expense' => fake()->randomFloat(2, 500, 5000),
            'total_income' => fake()->randomFloat(2, 5000, 50000),
        ];
    }
}
