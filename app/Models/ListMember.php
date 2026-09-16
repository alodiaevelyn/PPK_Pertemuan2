<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListMember extends Model
{
    protected $table = 'list_members';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'list_id',
        'user_id',
        'role',
        'joined_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}