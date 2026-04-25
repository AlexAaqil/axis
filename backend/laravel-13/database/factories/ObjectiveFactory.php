<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Objective;
use App\Models\Quarter;
use App\Enums\Priority;
use App\Enums\Status;

class ObjectiveFactory extends Factory
{
    protected $model = Objective::class;

    public function definition(): array
    {
        $quarter = Quarter::inRandomOrder()->first() ?? Quarter::factory()->create();
        
        $priorities = Priority::values();
        $statuses = Status::values();

        return [
            'uuid' => (string) Str::uuid(),
            'label' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'color' => $this->faker->randomElement(['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6']),
            'icon' => $this->faker->randomElement(['list', 'check-circle', 'flag', 'star', 'heart', 'clock', 'calendar', 'target', 'trending-up']),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'priority' => $this->faker->randomElement($priorities),
            'status' => $this->faker->randomElement($statuses),
            'start_date' => $this->faker->optional()->dateTimeBetween($quarter->start_date, $quarter->end_date),
            'due_date' => $this->faker->optional()->dateTimeBetween($quarter->start_date, $quarter->end_date),
            'completed_at' => null,
            'quarter_id' => $quarter->id,
        ];
    }

    /**
     * Set specific priority.
     */
    public function priority(Priority $priority): static
    {
        return $this->state(fn(array $attributes) => [
            'priority' => $priority->value,
        ]);
    }

    /**
     * Set specific status.
     */
    public function status(Status $status): static
    {
        return $this->state(function(array $attributes) use ($status) {
            $data = ['status' => $status->value];
            
            if ($status === Status::DONE) {
                $data['completed_at'] = now();
            }
            
            return $data;
        });
    }

    /**
     * Mark as completed.
     */
    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => Status::DONE->value,
            'completed_at' => now(),
        ]);
    }
}