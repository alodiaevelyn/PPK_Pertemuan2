<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPriorityAndDueDateTimeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * SRS-003 (AC-1): Pengguna dapat menetapkan prioritas tugas (low, medium, high)
     */
    public function test_user_can_set_task_priority(): void
    {
        $user = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('tasks.store', $list), [
            'title' => 'Tugas Sangat Mendesak',
            'priority' => 'high',
            'due_date' => now()->addDays(3)->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'list_id' => $list->id,
            'title' => 'Tugas Sangat Mendesak',
            'priority' => TaskPriority::HIGH->value,
            'created_by' => $user->id,
        ]);
    }

    /**
     * SRS-003: Validasi menolak prioritas yang tidak valid
     */
    public function test_task_priority_validation_rejects_invalid_values(): void
    {
        $user = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('tasks.store', $list), [
            'title' => 'Tugas Prioritas Salah',
            'priority' => 'prioritas_palsu',
        ]);

        $response->assertSessionHasErrors(['priority']);
    }

    /**
     * SRS-003 (AC-2): Pengguna dapat menentukan waktu tugas (due_date)
     */
    public function test_user_can_set_task_due_date(): void
    {
        $user = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $user->id]);
        $deadline = now()->addDays(5)->startOfHour();

        $this->actingAs($user)->post(route('tasks.store', $list), [
            'title' => 'Tugas dengan Tenggat Waktu',
            'priority' => 'medium',
            'due_date' => $deadline->format('Y-m-d H:i:s'),
        ]);

        $task = Task::where('title', 'Tugas dengan Tenggat Waktu')->first();
        $this->assertNotNull($task);
        $this->assertNotNull($task->due_date);
        $this->assertEquals($deadline->format('Y-m-d H:i'), $task->due_date->format('Y-m-d H:i'));
    }

    /**
     * SRS-003: Helper Domain mendeteksi apakah tugas terlewat tenggat waktu (Overdue)
     */
    public function test_task_detects_overdue_correctly(): void
    {
        $overdueTask = Task::factory()->create([
            'due_date' => now()->subDay(),
            'status' => TaskStatus::TODO->value,
        ]);

        $futureTask = Task::factory()->create([
            'due_date' => now()->addDay(),
            'status' => TaskStatus::TODO->value,
        ]);

        $completedOverdueTask = Task::factory()->create([
            'due_date' => now()->subDay(),
            'status' => TaskStatus::COMPLETED->value,
        ]);

        $this->assertTrue($overdueTask->isOverdue());
        $this->assertFalse($futureTask->isOverdue());
        $this->assertFalse($completedOverdueTask->isOverdue());
    }
}
