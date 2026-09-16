<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListProgressMonitoringTest extends TestCase
{
    use RefreshDatabase;

    /**
     * SRS-005: Edge case daftar tanpa tugas menghasilkan progres 0% (tidak division by zero)
     */
    public function test_list_progress_is_zero_when_no_tasks(): void
    {
        $list = TaskList::factory()->create();

        $this->assertEquals(0, $list->progress_percentage);
        $this->assertEquals(0, $list->completed_tasks_count);
        $this->assertEquals(0, $list->total_tasks_count);
    }

    /**
     * SRS-005 (AC-1): Menghitung persentase progres penyelesaian dengan akurat
     */
    public function test_list_calculates_correct_progress_percentage(): void
    {
        $list = TaskList::factory()->create();

        // 3 selesai, 1 belum selesai -> 75%
        Task::factory()->count(3)->completed()->create(['list_id' => $list->id]);
        Task::factory()->count(1)->create(['list_id' => $list->id]);

        $list->refresh();

        $this->assertEquals(4, $list->total_tasks_count);
        $this->assertEquals(3, $list->completed_tasks_count);
        $this->assertEquals(75, $list->progress_percentage);
    }

    /**
     * SRS-005: Menghasilkan 100% saat semua tugas selesai
     */
    public function test_list_calculates_100_percent_when_all_completed(): void
    {
        $list = TaskList::factory()->create();
        Task::factory()->count(4)->completed()->create(['list_id' => $list->id]);

        $list->refresh();

        $this->assertEquals(100, $list->progress_percentage);
    }

    /**
     * SRS-005 (AC-1): Pemilik daftar dapat mengakses halaman pemantauan progres
     */
    public function test_list_owner_can_view_progress_page(): void
    {
        $owner = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $owner->id]);

        Task::factory()->count(2)->completed()->create(['list_id' => $list->id]);
        Task::factory()->count(2)->create(['list_id' => $list->id]);

        $response = $this->actingAs($owner)->get(route('lists.progress', $list));

        $response->assertOk();
        $response->assertSee('Progres Penyelesaian Tugas');
        $response->assertSee('50%');
        $response->assertSee('2 dari 4 tugas telah diselesaikan');
    }

    /**
     * SRS-005: Keamanan/Otorisasi - Pengguna lain dilarang memantau daftar milik orang lain
     */
    public function test_non_owner_cannot_view_progress_page(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->get(route('lists.progress', $list));

        $response->assertStatus(403);
    }
}
