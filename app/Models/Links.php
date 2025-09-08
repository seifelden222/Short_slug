<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Links extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'slug',
        'target_url',
        'is_active',
        'expires_at',
        'clicks_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function clicks()
    {
        return $this->hasMany(Click::class);
    }

    public function scopeSearch(Builder $q, ?string $s): Builder
    {
        if (blank($s)) return $q;
        $s = trim($s);
        return $q->where(function (Builder $qq) use ($s) {
            $qq->where('slug', 'like', "%{$s}%")
                ->orWhere('target_url', 'like', "%{$s}%");
        });
    }

    public function scopeActive(Builder $q, $val): Builder
    {
        if (blank($val)) return $q;
        $bool = filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return $bool === null ? $q : $q->where('is_active', $bool);
    }

    public function scopeExpired(Builder $q, $val): Builder
    {
        if (blank($val)) return $q;
        $bool = filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($bool === null) return $q;
        $now = now();
        return $bool
            ? $q->where('expires_at', '<', $now)
            : $q->where(function (Builder $qq) use ($now) {
                $qq->where('expires_at', '>', $now)->orWhereNull('expires_at');
            });
    }

    public function scopeUserId(Builder $q, $userId): Builder
    {
        return blank($userId) ? $q : $q->where('user_id', $userId);
    }
}
