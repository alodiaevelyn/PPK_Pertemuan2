<?php

namespace Tests\Unit;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskModelDomainLogicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji casting enum prioritas dan status
     */
    public function test_task_casts_priority_and_status_to_enums(): void
    {
        $task = Task::factory()->create([
            'priority' => 'high',
            'status' => 'in_progress',
        ]);

        $this->assertInstanceOf(TaskPriority::class, $task->priority);
        $this->assertEquals(TaskPriority::HIGH, $task->priority);
        $this->assertEquals('Tinggi', $task->priority->label());

        $this->assertInstanceOf(TaskStatus::class, $task->status);
        $this->assertEquals(TaskStatus::IN_PROGRESS, $task->status);
        $this->assertEquals('Sedang Dikerjakan', $task->status->label());
    }

    /**
     * Uji method markAsCompleted() dan markAsTodo() (SRS-004)
     */
    public function test_task_mark_as_completed_and_todo(): void
    {
        $task = Task::factory()->create([
            'status' => TaskStatus::TODO->value,
            'completed_at' => null,
        ]);

        $this->assertFalse($task->isCompleted());
        $this->assertNull($task->completed_at);

        $task->markAsCompleted();
        $task->refresh();

        $this->assertTrue($task->isCompleted());
        $this->assertEquals(TaskStatus::COMPLETED, $task->status);
        $this->assertNotNull($task->completed_at);

        $task->markAsTodo();
        $task->refresh();

        $this->assertFalse($task->isCompleted());
        $this->assertEquals(TaskStatus::TODO, $task->status);
        $this->assertNull($task->completed_at);
    }

    /**
     * Uji toggleComplete() (SRS-004)
     */
    public function test_task_toggle_complete(): void
    {
        $task = Task::factory()->create([
            'status' => TaskStatus::TODO->value,
        ]);

        // Toggle pertama: todo -> completed
        $task->toggleComplete();
        $task->refresh();
        $this->assertTrue($task->isCompleted());

        // Toggle kedua: completed -> todo
        $task->toggleComplete();
        $task->refresh();
        $this->assertFalse($task->isCompleted());
    }

    /**
     * Uji logika deteksi overdue (SRS-003)
     */
    public function test_task_is_overdue_logic(): void
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

    /**
     * Uji kalkulasi progres tugas pada TaskList (SRS-005)
     */
    public function test_task_list_progress_percentage_calculation(): void
    {
        $list = TaskList::factory()->create();

        // 0 tasks = 0%
        $this->assertEquals(0, $list->progress_percentage);
        $this->assertEquals(0, $list->total_tasks_count);

        // Buat 4 tugas: 2 selesai, 1 in_progress, 1 todo
        Task::factory()->count(2)->completed()->create(['list_id' => $list->id]);
        Task::factory()->count(1)->inProgress()->create(['list_id' => $list->id]);
        Task::factory()->count(1)->create(['list_id' => $list->id, 'status' => TaskStatus::TODO->value]);

        $list->refresh();

        $this->assertEquals(4, $list->total_tasks_count);
        $this->assertEquals(2, $list->completed_tasks_count);
        $this->assertEquals(1, $list->in_progress_tasks_count);
        $this->assertEquals(1, $list->todo_tasks_count);
        $this->assertEquals(50, $list->progress_percentage); // 2/4 = 50%
    }
}
