<?php

namespace App\Models;

use App\Models\Traits\HasTaskProgress;
use Database\Factories\TaskListFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskList extends Model
{
    /** @use HasFactory<TaskListFactory> */
    use HasFactory;

    use HasTaskProgress;

    /**
     * Memetakan nama tabel sesuai ERD PM
     */
    protected $table = 'lists';

    protected $fillable = [
        'name',
        'description',
        'owner_id',
    ];

    // =========================================================================
    // RELASI ELOQUENT SESUAI ERD PM
    // =========================================================================

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'list_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(TaskCategory::class, 'list_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'list_members', 'list_id', 'user_id')
            ->withPivot('role', 'joined_at');
    }
}
