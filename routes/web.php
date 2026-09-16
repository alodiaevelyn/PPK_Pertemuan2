<?php

use App\Http\Controllers\ListProgressController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\TaskController;

Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth')->group(function () {

    // List / Project
    Route::get('/lists', [ListController::class, 'index']);
    Route::get('/lists/create', [ListController::class, 'create']);
    Route::post('/lists', [ListController::class, 'store']);

    // Task dalam project
    Route::get('/lists/{listId}/tasks/create', [TaskController::class, 'create']);
    Route::post('/lists/{listId}/tasks', [TaskController::class, 'store']);
});

use App\Http\Controllers\ListMemberController;

Route::middleware('auth')->group(function () {

    // SRS-002
    Route::get(
        '/lists/{listId}/members',
        [ListMemberController::class, 'index']
    );

    Route::post(
        '/lists/{listId}/members',
        [ListMemberController::class, 'store']
    );
});
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout']);
// =========================================================================
// PROGRAMMER 2: Fitur Tugas & Pemantauan Progres (SRS-003, SRS-004, SRS-005)
// =========================================================================
Route::middleware(['auth'])->group(function () {
    // SRS-005: Pemantauan progres oleh pemilik daftar
    Route::get('/lists/{list}/progress', [ListProgressController::class, 'show'])
        ->name('lists.progress');

    // SRS-003: Menambah dan mengupdate tugas dalam list (prioritas & tenggat waktu)
    Route::post('/lists/{list}/tasks', [TaskController::class, 'store'])
        ->name('tasks.store');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])
        ->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])
        ->name('tasks.destroy');

    // SRS-004: Menandai tugas sebagai selesai / belum selesai
    Route::patch('/tasks/{task}/toggle-complete', [TaskController::class, 'toggleComplete'])
        ->name('tasks.toggle-complete');
});
