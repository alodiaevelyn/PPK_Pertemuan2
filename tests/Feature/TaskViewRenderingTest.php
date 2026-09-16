<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskViewRenderingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * SRS-005: Halaman menampilkan progress bar visual dan metrik ringkasan
     */
    public function test_owner_sees_progress_dashboard_with_all_components(): void
    {
        $owner = User::factory()->create(['name' => 'Budi Santoso']);
        $list = TaskList::factory()->create([
            'owner_id' => $owner->id,
            'name' => 'Proyek Pengembangan Website',
            'description' => 'Membangun aplikasi kolaboratif.',
        ]);

        Task::factory()->completed()->create([
            'list_id' => $list->id,
            'title' => 'Tugas Pertama Selesai',
        ]);
        Task::factory()->create([
            'list_id' => $list->id,
            'title' => 'Tugas Kedua Belum',
            'status' => TaskStatus::TODO->value,
        ]);

        $response = $this->actingAs($owner)->get(route('lists.progress', $list));

        $response->assertOk();
        $response->assertSee('Proyek Pengembangan Website');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Progres Penyelesaian Tugas');
        $response->assertSee('50%'); // 1 dari 2 = 50%
        $response->assertSee('1 dari 2 tugas telah diselesaikan');
        $response->assertSee('Tugas Pertama Selesai');
        $response->assertSee('Tugas Kedua Belum');
    }

    /**
     * SRS-003: Kartu tugas merender badge prioritas dan tenggat waktu
     */
    public function test_task_card_renders_priority_and_due_date(): void
    {
        $owner = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $owner->id]);
        $targetDate = now()->addDays(3);

        Task::factory()->create([
            'list_id' => $list->id,
            'title' => 'Tugas Berprioritas Tinggi',
            'priority' => TaskPriority::HIGH->value,
            'due_date' => $targetDate,
            'status' => TaskStatus::TODO->value,
        ]);

        $response = $this->actingAs($owner)->get(route('lists.progress', $list));

        $response->assertOk();
        $response->assertSee('Tugas Berprioritas Tinggi');
        $response->assertSee('Prioritas Tinggi');
        $response->assertSee($targetDate->translatedFormat('d M Y'));
    }

    /**
     * SRS-004: Tugas selesai menampilkan efek coret (line-through), badge Selesai, dan timestamp
     */
    public function test_task_card_renders_completed_status_with_strikethrough_and_timestamp(): void
    {
        $owner = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $owner->id]);

        $completedTask = Task::factory()->completed()->create([
            'list_id' => $list->id,
            'title' => 'Tugas Yang Sudah Rampung',
        ]);

        $response = $this->actingAs($owner)->get(route('lists.progress', $list));

        $response->assertOk();
        $response->assertSee('Tugas Yang Sudah Rampung');
        $response->assertSee('line-through');
        $response->assertSee('Selesai');
        $response->assertSee($completedTask->completed_at->translatedFormat('d M'));
    }

    /**
     * SRS-003: Kartu tugas merender peringatan Terlambat (Overdue) jika melewati batas waktu
     */
    public function test_task_card_renders_overdue_warning_when_past_deadline(): void
    {
        $owner = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $owner->id]);

        Task::factory()->overdue()->create([
            'list_id' => $list->id,
            'title' => 'Tugas Terlambat Nih',
        ]);

        $response = $this->actingAs($owner)->get(route('lists.progress', $list));

        $response->assertOk();
        $response->assertSee('Tugas Terlambat Nih');
        $response->assertSee('(Terlambat)');
    }
}
