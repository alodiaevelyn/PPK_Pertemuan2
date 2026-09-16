<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListMember extends Model
{
    use HasFactory;

use Illuminate\Database\Eloquent\Model;

class ListMember extends Model
{
    protected $table = 'list_members';

    public $timestamps = false;

    protected $fillable = [
        'list_id',
        'user_id',
        'role',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function taskList(): BelongsTo
    {
        return $this->belongsTo(TaskList::class, 'list_id');
    }
}
    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function list(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TaskList::class, 'list_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
