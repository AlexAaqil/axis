<?php

namespace Database\Factories;

use App\Models\Quarter;
use App\Models\Year;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quarter>
 */
class QuarterFactory extends Factory
{
    protected $model = Quarter::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = Year::inRandomOrder()->first() ?? Year::factory()->create();

        $year_value = $year->year;
        $quarter_number = $this->faker->numberBetween(1,4);

        // Calculate quarter dates based on quarter number
        $start_month = ($quarter_number -1) * 3 + 1; // Q1 = 1, Q2 = 4, Q3 = 7, Q4 = 10
        $start_date = "{$year_value}-" . str_pad($start_month, 2, '0', STR_PAD_LEFT) . "-01";
        // Calculate end date (last day of quarter)
        $end_month = $start_month + 2;
        // Days in the month
        $last_day = date('t', strtotime("{$year_value} - {$end_month}-01"));
        $end_date = "{$year_value}-" . str_pad($end_month, 2, '0', STR_PAD_LEFT) . "-{$last_day}";

        return [
            'label' => "Q{$quarter_number}",
            'start_date' => $start_date,
            'end_date' => $end_date,
            'year_id' => $year->id,
        ];
    }

    /**
     * Configure a specific quarter for a specific year.
     * 
     * Usage: Quarter::factory()->forYear(2025, 1)->create()
     * 
     * This is a "state" method - customizes the factory.
     */
    public function forYear(int $year_value, int $quarter_number): static
    {
        return $this->state(function (array $attributes) use ($year_value, $quarter_number) {
            // Find or create the year
            $year = Year::firstOrCreate(['year' => $year_value]);
            
            // Calculate dates (same as above)
            $start_month = ($quarter_number - 1) * 3 + 1;
            $start_date = "{$year_value}-" . str_pad($start_month, 2, '0', STR_PAD_LEFT) . "-01";
            
            $end_month = $start_month + 2;
            $last_day = date('t', strtotime("{$year_value}-{$end_month}-01"));
            $end_date = "{$year_value}-" . str_pad($end_month, 2, '0', STR_PAD_LEFT) . "-{$last_day}";
            
            return [
                'label' => "Q{$quarter_number}",
                'start_date' => $start_date,
                'end_date' => $end_date,
                'year_id' => $year->id,
            ];
        });
    }
}
