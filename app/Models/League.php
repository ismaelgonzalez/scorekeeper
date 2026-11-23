<?php

namespace App\Models;

use Database\Factories\LeagueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class League extends Model
{
    /** @use HasFactory<LeagueFactory> */
    use HasFactory, SoftDeletes;

    public const array STATUSES = ['draft', 'active', 'completed', 'cancelled'];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
