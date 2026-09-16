<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('lists')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('task_categories')->nullOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();

            // SRS-003: Menentukan prioritas dan waktu tugas
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->dateTime('due_date')->nullable();

            // SRS-004: Menandai tugas sebagai selesai
            $table->enum('status', ['todo', 'in_progress', 'completed'])->default('todo');
            $table->dateTime('completed_at')->nullable();

            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Indexing untuk optimalisasi query filtering & monitoring progres (SRS-005)
            $table->index(['list_id', 'status']);
            $table->index(['list_id', 'priority']);
            $table->index(['list_id', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
