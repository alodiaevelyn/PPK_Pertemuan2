<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'tasks';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    protected $fillable = [
        'list_id',
        'category_id',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'created_by',
        'completed_at',
    ];
}

    /**
     * Casting atribut ke Enum dan tipe data waktu
     */
    protected function casts(): array
    {
        return [
            'priority' => TaskPriority::class,
            'status' => TaskStatus::class,
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    // =========================================================================
    // RELASI ELOQUENT SESUAI ERD PM
    // =========================================================================

    public function taskList(): BelongsTo
    {
        return $this->belongsTo(TaskList::class, 'list_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TaskCategory::class, 'category_id');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignments', 'task_id', 'user_id')
            ->withPivot('assigned_at');
    }

    // =========================================================================
    // LOGIKA BISNIS STATUS PENYELESAIAN TUGAS (SRS-004)
    // =========================================================================

    /**
     * Memeriksa apakah tugas sudah berstatus selesai
     */
    public function isCompleted(): bool
    {
        return $this->status === TaskStatus::COMPLETED;
    }

    /**
     * Menandai tugas sebagai selesai dan mencatat completed_at (SRS-004)
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => TaskStatus::COMPLETED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mengembalikan status tugas menjadi todo (SRS-004)
     */
    public function markAsTodo(): void
    {
        $this->update([
            'status' => TaskStatus::TODO,
            'completed_at' => null,
        ]);
    }

    /**
     * Mengubah status tugas menjadi in_progress
     */
    public function markAsInProgress(): void
    {
        $this->update([
            'status' => TaskStatus::IN_PROGRESS,
            'completed_at' => null,
        ]);
    }

    /**
     * Melakukan toggle status penyelesaian (SRS-004)
     */
    public function toggleComplete(): void
    {
        if ($this->isCompleted()) {
            $this->markAsTodo();
        } else {
            $this->markAsCompleted();
        }
    }

    // =========================================================================
    // LOGIKA PENGECEKAN TENGGAT WAKTU (SRS-003)
    // =========================================================================

    /**
     * Memeriksa apakah batas waktu tugas telah terlewat dan tugas belum selesai
     */
    public function isOverdue(): bool
    {
        if ($this->isCompleted() || ! $this->due_date) {
            return false;
        }

        return $this->due_date->isPast();
    }

    // =========================================================================
    // QUERY SCOPES
    // =========================================================================

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', TaskStatus::COMPLETED->value);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [TaskStatus::TODO->value, TaskStatus::IN_PROGRESS->value]);
    }

    public function scopeByPriority(Builder $query, TaskPriority $priority): Builder
    {
        return $query->where('priority', $priority->value);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', '!=', TaskStatus::COMPLETED->value)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }
}
