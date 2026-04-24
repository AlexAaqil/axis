<?php

namespace Database\Factories;

use App\Models\Year;
use Illuminate\Database\Eloquent\Factories\Factory;

class YearFactory extends Factory
{
    protected $model = Year::class;

    public function definition(): array
    {
        return [
            'year' => $this->faker->unique()->numberBetween(2000, 2030),
        ];
    }
}