<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerAndListProgressTest extends TestCase
{
    use RefreshDatabase;

    /**
     * SRS-003: Menyimpan tugas dengan prioritas dan batas waktu
     */
    public function test_user_can_store_task_with_priority_and_due_date(): void
    {
        $user = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $user->id]);
        $dueDate = now()->addDays(5)->startOfHour();

        $response = $this->actingAs($user)->post(route('tasks.store', $list), [
            'title' => 'Implementasi Modul Controller',
            'description' => 'Menyelesaikan fungsi store dan update.',
            'priority' => 'high',
            'due_date' => $dueDate->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'list_id' => $list->id,
            'title' => 'Implementasi Modul Controller',
            'priority' => TaskPriority::HIGH->value,
            'status' => TaskStatus::TODO->value,
            'created_by' => $user->id,
        ]);
    }

    /**
     * SRS-003: Validasi menolak prioritas yang salah
     */
    public function test_store_task_validation_fails_with_invalid_priority(): void
    {
        $user = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('tasks.store', $list), [
            'title' => 'Tugas Prioritas Aneh',
            'priority' => 'super_critical_invalid',
        ]);

        $response->assertSessionHasErrors(['priority']);
    }

    /**
     * Mengupdate tugas (prioritas, judul, deskripsi)
     */
    public function test_user_can_update_task(): void
    {
        $user = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $user->id]);
        $task = Task::factory()->create([
            'list_id' => $list->id,
            'priority' => TaskPriority::LOW->value,
            'status' => TaskStatus::TODO->value,
        ]);

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => 'Judul Diperbarui',
            'priority' => 'high',
            'status' => 'completed',
        ]);

        $response->assertRedirect();
        $task->refresh();

        $this->assertEquals('Judul Diperbarui', $task->title);
        $this->assertEquals(TaskPriority::HIGH, $task->priority);
        $this->assertEquals(TaskStatus::COMPLETED, $task->status);
        $this->assertNotNull($task->completed_at);
    }

    /**
     * SRS-004: Menandai tugas sebagai selesai atau belum selesai via toggleComplete
     */
    public function test_user_can_toggle_complete_task(): void
    {
        $user = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $user->id]);
        $task = Task::factory()->create([
            'list_id' => $list->id,
            'status' => TaskStatus::TODO->value,
            'completed_at' => null,
        ]);

        // Toggle pertama: todo -> completed
        $response = $this->actingAs($user)->patch(route('tasks.toggle-complete', $task));
        $response->assertRedirect();
        $task->refresh();

        $this->assertEquals(TaskStatus::COMPLETED, $task->status);
        $this->assertNotNull($task->completed_at);

        // Toggle kedua: completed -> todo
        $response2 = $this->actingAs($user)->patch(route('tasks.toggle-complete', $task));
        $response2->assertRedirect();
        $task->refresh();

        $this->assertEquals(TaskStatus::TODO, $task->status);
        $this->assertNull($task->completed_at);
    }

    /**
     * Menghapus tugas via destroy
     */
    public function test_user_can_destroy_task(): void
    {
        $user = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $user->id]);
        $task = Task::factory()->create(['list_id' => $list->id]);

        $response = $this->actingAs($user)->delete(route('tasks.destroy', $task));
        $response->assertRedirect();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /**
     * SRS-005: Pemilik daftar dapat mengakses endpoint pemantauan progres
     */
    public function test_list_owner_can_view_progress(): void
    {
        $owner = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $owner->id]);

        Task::factory()->count(2)->completed()->create(['list_id' => $list->id]);
        Task::factory()->count(2)->create(['list_id' => $list->id, 'status' => TaskStatus::TODO->value]);

        $response = $this->actingAs($owner)->getJson(route('lists.progress', $list));

        $response->assertOk();
        $response->assertJsonPath('statistics.total', 4);
        $response->assertJsonPath('statistics.completed', 2);
        $response->assertJsonPath('statistics.percentage', 50);
    }

    /**
     * SRS-005: Bukan pemilik dilarang mengakses pemantauan progres (403 Forbidden)
     */
    public function test_non_owner_forbidden_from_viewing_progress(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->get(route('lists.progress', $list));

        $response->assertForbidden();
    }

    /**
     * SRS-005: Filter progres berdasarkan status dan prioritas
     */
    public function test_list_progress_can_be_filtered_by_status_and_priority(): void
    {
        $owner = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $owner->id]);

        Task::factory()->create([
            'list_id' => $list->id,
            'title' => 'Tugas Selesai Tinggi',
            'status' => TaskStatus::COMPLETED->value,
            'priority' => TaskPriority::HIGH->value,
        ]);

        Task::factory()->create([
            'list_id' => $list->id,
            'title' => 'Tugas Todo Rendah',
            'status' => TaskStatus::TODO->value,
            'priority' => TaskPriority::LOW->value,
        ]);

        // Filter status completed
        $responseStatus = $this->actingAs($owner)->getJson(route('lists.progress', [$list, 'status' => 'completed']));
        $responseStatus->assertOk();
        $responseStatus->assertJsonCount(1, 'tasks');
        $responseStatus->assertJsonPath('tasks.0.title', 'Tugas Selesai Tinggi');

        // Filter priority low
        $responsePriority = $this->actingAs($owner)->getJson(route('lists.progress', [$list, 'priority' => 'low']));
        $responsePriority->assertOk();
        $responsePriority->assertJsonCount(1, 'tasks');
        $responsePriority->assertJsonPath('tasks.0.title', 'Tugas Todo Rendah');
    }
}
