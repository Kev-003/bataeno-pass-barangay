<?php

namespace Database\Factories;

use App\Models\DocumentTypeProperty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentTypeProperty>
 */
class DocumentTypePropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = DocumentTypeProperty::class;
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->regexify('[A-Z]{3}-[0-9]{4}'),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(10),
            'default_fee' => $this->faker->numberBetween(50, 500),
            'validity_days' => $this->faker->numberBetween(7, 365),
        ];
    }
}
