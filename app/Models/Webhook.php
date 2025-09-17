<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory;
    protected $fillable = [
        'link_id',
        'payload',
        'event',
        'target_url',
        'status',
        'attempts',
        'last_error',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
    public function link()
    {
        return $this->belongsTo(Link::class);
    }
}
