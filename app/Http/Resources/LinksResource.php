<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LinksResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'slug' => $this->slug,
            'short_url' => url("/r/{$this->slug}"),
            'target_url' => $this->target_url,
            'is_active' => (bool) $this->is_active,
            'expires_at' => optional($this->expires_at)->toIso8601String(),
            'clicks_count' => (int) $this->clicks_count,
            'is_expired'   => (bool) ($this->expires_at?->isPast()),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'deleted_at' => optional($this->deleted_at)->toIso8601String(),
        ];
    }
}
