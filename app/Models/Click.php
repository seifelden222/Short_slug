<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Click extends Model
{
    use HasFactory;

    protected $fillable = [
        'link_id',
        'occurred_at',
        'ip',
        'ua',
        'referrer',
        'country',
        'idempotency_key',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function link(): BelongsTo
    {
        return $this->belongsTo(Links::class);
    }
}
