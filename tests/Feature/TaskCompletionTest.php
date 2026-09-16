<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCompletionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * SRS-004 (AC-1): Pengguna dapat menandai tugas yang telah selesai
     */
    public function test_user_can_mark_task_as_completed(): void
    {
        $user = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $user->id]);
        $task = Task::factory()->create([
            'list_id' => $list->id,
            'status' => TaskStatus::TODO->value,
            'completed_at' => null,
        ]);

        $response = $this->actingAs($user)->patch(route('tasks.toggle-complete', $task));

        $response->assertRedirect();
        $task->refresh();

        $this->assertEquals(TaskStatus::COMPLETED, $task->status);
        $this->assertTrue($task->isCompleted());
        $this->assertNotNull($task->completed_at);
    }

    /**
     * SRS-004: Pengguna dapat membatalkan tanda selesai (toggle uncheck)
     */
    public function test_user_can_mark_task_as_incomplete(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->completed()->create();

        $this->actingAs($user)->patch(route('tasks.toggle-complete', $task));

        $task->refresh();
        $this->assertEquals(TaskStatus::TODO, $task->status);
        $this->assertFalse($task->isCompleted());
        $this->assertNull($task->completed_at);
    }

    /**
     * SRS-004 (AC-2): Status tugas menunjukkan bahwa tugas telah selesai pada tampilan UI
     */
    public function test_task_status_shows_completed_correctly_on_view(): void
    {
        $user = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $user->id]);
        $completedTask = Task::factory()->completed()->create([
            'list_id' => $list->id,
            'title' => 'Tugas Praktikum Selesai',
        ]);

        $response = $this->actingAs($user)->get(route('lists.progress', $list));

        $response->assertOk();
        $response->assertSee('Tugas Praktikum Selesai');
        $response->assertSee('Selesai');
        $response->assertSee('line-through');
    }
}
