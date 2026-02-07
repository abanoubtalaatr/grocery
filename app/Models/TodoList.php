<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TodoList extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'completed',
        'due_date',
        'order',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'due_date' => 'date',
        'order' => 'integer',
    ];

    /**
     * Get the user that owns the todo item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
