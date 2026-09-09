<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'list_id' => TaskList::factory(),
            'category_id' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(TaskPriority::cases())->value,
            'status' => TaskStatus::TODO->value,
            'due_date' => fake()->dateTimeBetween('now', '+2 weeks'),
            'created_by' => User::factory(),
            'completed_at' => null,
        ];
    }

    /**
     * State untuk tugas yang telah selesai (SRS-004)
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::COMPLETED->value,
            'completed_at' => now(),
        ]);
    }

    /**
     * State untuk tugas sedang dikerjakan
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::IN_PROGRESS->value,
            'completed_at' => null,
        ]);
    }

    /**
     * State untuk tugas prioritas tinggi (SRS-003)
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => TaskPriority::HIGH->value,
        ]);
    }

    /**
     * State untuk tugas yang terlewat tenggat waktu (Overdue)
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::TODO->value,
            'due_date' => now()->subDays(2),
            'completed_at' => null,
        ]);
    }
}
