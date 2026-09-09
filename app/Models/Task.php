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