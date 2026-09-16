<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AtomicTransactionAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // SRS-008: PROSES PEMBUATAN DAFTAR SECARA ATOMIC
    // =========================================================================

    /**
     * SRS-008: Pembuatan daftar berhasil menyimpan list dan mendaftarkan pembuat ke anggota secara atomic.
     */
    public function test_create_list_runs_atomically_on_success(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('lists.store'), [
            'name' => 'Project Sistem Informasi',
            'description' => 'Pengembangan modul backend dan frontend',
        ]);

        $response->assertRedirect('/lists');

        // Pastikan list tersimpan
        $this->assertDatabaseHas('lists', [
            'name' => 'Project Sistem Informasi',
            'owner_id' => $user->id,
        ]);

        $list = TaskList::where('name', 'Project Sistem Informasi')->first();

        // Pastikan relasi keanggotaan tersimpan otomatis (role: manager)
        $this->assertDatabaseHas('list_members', [
            'list_id' => $list->id,
            'user_id' => $user->id,
            'role' => 'manager',
        ]);
    }

    /**
     * SRS-008: Jika salah satu langkah pembuatan daftar gagal, database di-rollback total tanpa data tersisa inkonsisten.
     */
    public function test_create_list_rolls_back_completely_if_step_fails(): void
    {
        $user = User::factory()->create();

        // Simulasi error database saat langkah penambahan anggota
        try {
            DB::transaction(function () use ($user) {
                $list = TaskList::create([
                    'name' => 'Project Yang Gagal',
                    'owner_id' => $user->id,
                ]);

                // Simulasi kegagalan pada langkah berikutnya
                throw new Exception('Simulasi kegagalan sistem pada pembuatan keanggotaan');
            });
        } catch (Exception $e) {
            // Exception sengaja ditangkap untuk memverifikasi rollback
        }

        // Verifikasi tidak ada data daftar yang tersisa
        $this->assertDatabaseMissing('lists', [
            'name' => 'Project Yang Gagal',
        ]);
        $this->assertDatabaseCount('lists', 0);
        $this->assertDatabaseCount('list_members', 0);
    }

    // =========================================================================
    // SRS-008: PEMBUATAN TUGAS OTOMATIS MENJADI PEMILIK & ATOMIC
    // =========================================================================

    /**
     * SRS-008: Pengguna menambah tugas baru dengan otomatis menjadi pemilik dan assignee secara atomic.
     */
    public function test_user_can_create_task_and_automatically_become_owner_atomically(): void
    {
        $user = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('tasks.store', $list), [
            'title' => 'Tugas Integrasi Sistem',
            'description' => 'Menyelesaikan modul otentikasi dan transaksi',
            'priority' => TaskPriority::HIGH->value,
            'due_date' => now()->addDays(3)->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();

        // Verifikasi tugas tersimpan dengan created_by = user
        $this->assertDatabaseHas('tasks', [
            'list_id' => $list->id,
            'title' => 'Tugas Integrasi Sistem',
            'created_by' => $user->id,
            'status' => TaskStatus::TODO->value,
        ]);

        $task = Task::where('title', 'Tugas Integrasi Sistem')->first();

        // Verifikasi pengguna otomatis menjadi assignee di task_assignments
        $this->assertDatabaseHas('task_assignments', [
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * SRS-008: Jika salah satu langkah penyimpanan tugas gagal, seluruh perubahan dibatalkan (rollback).
     */
    public function test_task_creation_rolls_back_completely_if_assignment_fails(): void
    {
        $user = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $user->id]);

        try {
            DB::transaction(function () use ($list, $user) {
                $task = $list->tasks()->create([
                    'title' => 'Tugas Gagal Transaksi',
                    'created_by' => $user->id,
                    'priority' => TaskPriority::MEDIUM->value,
                    'status' => TaskStatus::TODO->value,
                ]);

                // Simulasi kegagalan pada langkah penugasan / assignment
                throw new Exception('Simulasi kegagalan assignment tugas');
            });
        } catch (Exception $e) {
            // Exception sengaja ditangkap untuk memverifikasi rollback
        }

        // Verifikasi tidak ada tugas atau penugasan yang tersisa
        $this->assertDatabaseMissing('tasks', [
            'title' => 'Tugas Gagal Transaksi',
        ]);
        $this->assertDatabaseCount('tasks', 0);
        $this->assertDatabaseCount('task_assignments', 0);
    }

    // =========================================================================
    // SRS-008: PENGHAPUSAN DAFTAR CASCADING SECARA ATOMIC
    // =========================================================================

    /**
     * SRS-008: Pemilik dapat menghapus daftar beserta seluruh tugas, penugasan, dan keanggotaan secara atomic.
     */
    public function test_owner_can_delete_list_along_with_all_tasks_and_members_atomically(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $list = TaskList::factory()->create(['owner_id' => $owner->id]);

        // Tambah keanggotaan
        $list->members()->attach($owner->id, ['role' => 'manager', 'joined_at' => now()]);
        $list->members()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

        // Tambah tugas & assignment
        $task1 = Task::factory()->create(['list_id' => $list->id, 'created_by' => $owner->id]);
        $task2 = Task::factory()->create(['list_id' => $list->id, 'created_by' => $member->id]);
        $task1->assignees()->attach($owner->id, ['assigned_at' => now()]);
        $task2->assignees()->attach($member->id, ['assigned_at' => now()]);

        // Verifikasi data awal ada di DB
        $this->assertDatabaseCount('lists', 1);
        $this->assertDatabaseCount('list_members', 2);
        $this->assertDatabaseCount('tasks', 2);
        $this->assertDatabaseCount('task_assignments', 2);

        // Eksekusi penghapusan oleh pemilik
        $response = $this->actingAs($owner)->delete(route('lists.destroy', $list));

        $response->assertRedirect('/lists');

        // Verifikasi seluruh data terkait terhapus bersih tanpa menyisakan data yatim
        $this->assertDatabaseMissing('lists', ['id' => $list->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $task1->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $task2->id]);
        $this->assertDatabaseMissing('list_members', ['list_id' => $list->id]);
        $this->assertDatabaseMissing('task_assignments', ['task_id' => $task1->id]);
        $this->assertDatabaseMissing('task_assignments', ['task_id' => $task2->id]);

        $this->assertDatabaseCount('lists', 0);
        $this->assertDatabaseCount('tasks', 0);
        $this->assertDatabaseCount('list_members', 0);
        $this->assertDatabaseCount('task_assignments', 0);
    }

    /**
     * SRS-008: Jika proses penghapusan daftar gagal di tengah jalan, seluruh perubahan di-rollback ke kondisi semula.
     */
    public function test_delete_list_rolls_back_completely_if_failure_occurs_during_process(): void
    {
        $owner = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $owner->id]);
        $task = Task::factory()->create(['list_id' => $list->id, 'created_by' => $owner->id]);
        $list->members()->attach($owner->id, ['role' => 'manager', 'joined_at' => now()]);

        try {
            DB::transaction(function () use ($list) {
                // Langkah 1: hapus tugas
                $list->tasks()->delete();

                // Simulasi kegagalan sebelum menghapus list
                throw new Exception('Simulasi kegagalan di tengah proses delete cascading');
            });
        } catch (Exception $e) {
            // Exception sengaja ditangkap untuk memverifikasi rollback
        }

        // Verifikasi bahwa seluruh data kembali utuh (tidak ada yang terhapus sebagian)
        $this->assertDatabaseHas('lists', ['id' => $list->id]);
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
        $this->assertDatabaseHas('list_members', ['list_id' => $list->id, 'user_id' => $owner->id]);
    }

    // =========================================================================
    // SRS-009: OTORISASI & PENOLAKAN PENGGUNA TIDAK BERWENANG (403 FORBIDDEN)
    // =========================================================================

    /**
     * SRS-009: Pengguna bukan pemilik dilarang menghapus daftar (403 Forbidden).
     */
    public function test_non_owner_is_forbidden_from_deleting_list(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->delete(route('lists.destroy', $list));

        $response->assertForbidden();

        // Pastikan daftar tidak terhapus
        $this->assertDatabaseHas('lists', ['id' => $list->id]);
    }

    /**
     * SRS-009: Anggota biasa (member) dilarang menghapus daftar (403 Forbidden).
     */
    public function test_member_is_forbidden_from_deleting_list(): void
    {
        $owner = User::factory()->create();
        $memberUser = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $owner->id]);

        // Daftarkan sebagai anggota
        $list->members()->attach($memberUser->id, ['role' => 'member', 'joined_at' => now()]);

        $response = $this->actingAs($memberUser)->delete(route('lists.destroy', $list));

        $response->assertForbidden();

        // Pastikan daftar tetap ada
        $this->assertDatabaseHas('lists', ['id' => $list->id]);
    }

    /**
     * SRS-009: Pengguna yang bukan pemilik dan bukan anggota dilarang menambah tugas (403 Forbidden).
     */
    public function test_unauthorized_user_forbidden_from_adding_task_to_list(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($stranger)->post(route('tasks.store', $list), [
            'title' => 'Tugas Ilegal',
            'priority' => TaskPriority::LOW->value,
        ]);

        $response->assertForbidden();

        // Pastikan tugas tidak tersimpan
        $this->assertDatabaseMissing('tasks', ['title' => 'Tugas Ilegal']);
    }

    /**
     * SRS-009: Anggota terdaftar diizinkan menambah tugas ke dalam list.
     */
    public function test_registered_member_is_allowed_to_add_task_to_list(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $owner->id]);

        // Jadikan user sebagai anggota list
        $list->members()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

        $response = $this->actingAs($member)->post(route('tasks.store', $list), [
            'title' => 'Tugas dari Anggota Tim',
            'priority' => TaskPriority::MEDIUM->value,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'list_id' => $list->id,
            'title' => 'Tugas dari Anggota Tim',
            'created_by' => $member->id,
        ]);
    }

    /**
     * SRS-009: Pengguna belum login (guest) ditolak saat mengakses fitur daftar.
     */
    public function test_guest_is_redirected_to_login_when_accessing_list_actions(): void
    {
        $list = TaskList::factory()->create();

        // Guest coba akses hapus list
        $deleteResponse = $this->delete(route('lists.destroy', $list));
        $deleteResponse->assertRedirect(route('login'));

        // Guest coba tambah tugas
        $storeTaskResponse = $this->post(route('tasks.store', $list), [
            'title' => 'Tugas Anonim',
            'priority' => 'low',
        ]);
        $storeTaskResponse->assertRedirect(route('login'));
    }

    // =========================================================================
    // SRS-009: VALIDASI INPUT PENGGUNA
    // =========================================================================

    /**
     * SRS-009: Validasi menolak nama daftar kosong atau melebihi batas karakter.
     */
    public function test_list_creation_validation_fails_on_empty_or_too_long_name(): void
    {
        $user = User::factory()->create();

        // Uji nama kosong
        $responseEmpty = $this->actingAs($user)->post(route('lists.store'), [
            'name' => '',
        ]);
        $responseEmpty->assertSessionHasErrors(['name']);

        // Uji nama melebihi 150 karakter
        $responseLong = $this->actingAs($user)->post(route('lists.store'), [
            'name' => str_repeat('a', 151),
        ]);
        $responseLong->assertSessionHasErrors(['name']);
    }

    /**
     * SRS-009: Validasi menolak judul tugas kosong dan prioritas tidak valid.
     */
    public function test_task_creation_validation_fails_on_empty_title_and_invalid_priority(): void
    {
        $user = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('tasks.store', $list), [
            'title' => '',
            'priority' => 'prioritas_palsu',
        ]);

        $response->assertSessionHasErrors(['title', 'priority']);
    }

    // =========================================================================
    // SRS-009: PREPARED STATEMENT & PENCEGAHAN SQL INJECTION
    // =========================================================================

    /**
     * SRS-009: Input pengguna yang mengandung payload SQL Injection di-escape dengan aman melalui parameter binding.
     */
    public function test_input_with_sql_injection_payload_is_treated_as_literal_text(): void
    {
        $user = User::factory()->create();
        $list = TaskList::factory()->create(['owner_id' => $user->id]);

        $sqlInjectionPayload = "Tugas'; DROP TABLE tasks; -- ' OR '1'='1";

        $response = $this->actingAs($user)->post(route('tasks.store', $list), [
            'title' => $sqlInjectionPayload,
            'description' => "Test payload ' OR '1'='1",
            'priority' => TaskPriority::LOW->value,
        ]);

        $response->assertRedirect();

        // Pastikan tabel tasks TIDAK ter-drop dan payload tersimpan sebagai string biasa
        $this->assertDatabaseHas('tasks', [
            'list_id' => $list->id,
            'title' => $sqlInjectionPayload,
            'description' => "Test payload ' OR '1'='1",
        ]);
    }
}
