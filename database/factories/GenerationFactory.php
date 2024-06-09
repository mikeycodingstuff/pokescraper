<?php

namespace Database\Factories;

use App\Helpers\RomanNumeralsConverter;
use App\Models\Generation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Generation>
 */
class GenerationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'placeholder',
            'main_region' => fake()->country,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Generation $generation) {
            $generation->name = 'generation-' . RomanNumeralsConverter::convertToRomanNumeral($generation->id);
            $generation->save();
        });
    }
}
