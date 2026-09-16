<?php

namespace App\Models;

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