<?php

namespace Database\Factories;

use App\Models\TaskCategory;
use App\Models\TaskList;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskCategory>
 */
class TaskCategoryFactory extends Factory
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
            'name' => fake()->word(),
        ];
    }
}
