<?php

namespace Tests\Feature;

use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * SRS-006: Pengguna dapat membuat daftar tugas baru,
     * dan data daftar yang dibuat tersimpan dengan benar.
     */
    public function test_user_can_create_a_new_task_list(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/lists', [
            'name' => 'Project Skripsi',
            'description' => 'Daftar tugas untuk menyelesaikan skripsi.',
        ]);

        $response->assertRedirect('/lists');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('lists', [
            'name' => 'Project Skripsi',
            'description' => 'Daftar tugas untuk menyelesaikan skripsi.',
            'owner_id' => $user->id,
        ]);
    }

    /**
     * SRS-006: Pengguna yang membuat daftar otomatis menjadi pemilik daftar.
     */
    public function test_creator_automatically_becomes_the_owner(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/lists', [
            'name' => 'Daftar Belanja',
        ]);

        $list = TaskList::where('name', 'Daftar Belanja')->firstOrFail();

        $this->assertSame($user->id, $list->owner_id);
        $this->assertTrue($user->is($list->owner));

        // Pemilik juga tercatat sebagai anggota dengan role manager.
        $this->assertDatabaseHas('list_members', [
            'list_id' => $list->id,
            'user_id' => $user->id,
            'role' => 'manager',
        ]);
    }

    /**
     * SRS-006: Validasi - nama daftar wajib diisi.
     */
    public function test_name_is_required_when_creating_a_list(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/lists', [
            'description' => 'Tanpa nama',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('lists', 0);
    }

    /**
     * Hanya pengguna yang sudah login yang dapat membuat daftar tugas.
     */
    public function test_guest_cannot_create_a_list(): void
    {
        $response = $this->post('/lists', [
            'name' => 'Percobaan Guest',
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseCount('lists', 0);
    }
}