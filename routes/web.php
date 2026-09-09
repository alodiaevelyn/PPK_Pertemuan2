<?php

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