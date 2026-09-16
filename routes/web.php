<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\ListMemberController;
use App\Http\Controllers\ListProgressController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\AuthController;

Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// =========================================================================
// FITUR YANG MEMBUTUHKAN AUTENTIKASI
// =========================================================================
Route::middleware(['auth'])->group(function () {

    // SRS-001 & SRS-008: Membuat, Mengatur, dan Menghapus Daftar/Project Tugas
    Route::get('/lists', [ListController::class, 'index'])->name('lists.index');
    Route::get('/lists/create', [ListController::class, 'create'])->name('lists.create');
    Route::post('/lists', [ListController::class, 'store'])->name('lists.store');
    Route::delete('/lists/{list}', [ListController::class, 'destroy'])->name('lists.destroy');

    // SRS-005: Pemantauan progres oleh pemilik daftar
    Route::get('/lists/{list}/progress', [ListProgressController::class, 'show'])
        ->name('lists.progress');

    // SRS-002: Menambahkan pengguna ke dalam daftar tugas
    Route::get('/lists/{listId}/members', [ListMemberController::class, 'index'])
        ->name('lists.members.index');
    Route::post('/lists/{listId}/members', [ListMemberController::class, 'store'])
        ->name('lists.members.store');

    // SRS-003, SRS-008, SRS-009: Form dan simpan tugas baru
    Route::get('/lists/{list}/tasks/create', [TaskController::class, 'create'])
        ->name('tasks.create');
    Route::post('/lists/{list}/tasks', [TaskController::class, 'store'])
        ->name('tasks.store');

    // SRS-003 & SRS-004: Update, Delete, Toggle Complete Tugas
    Route::put('/tasks/{task}', [TaskController::class, 'update'])
        ->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])
        ->name('tasks.destroy');
    Route::patch('/tasks/{task}/toggle-complete', [TaskController::class, 'toggleComplete'])
        ->name('tasks.toggle-complete');

    // SRS-006: Manajemen akun pengguna oleh admin
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
});
