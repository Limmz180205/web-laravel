<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'year' => $this->year,
            'score' => $this->score,
            'genres' => $this->genres,
            'status' => $this->status,
            'total_units' => $this->total_units,
            'unit_name' => match ($this->type) {
                'anime', 'drama' => 'episode',
                'manga' => 'chapter',
                'light_novel' => 'volume',
            },
            'creator' => $this->creator,
            'progress' => $this->whenPivotLoaded('media_user', fn () => $this->pivot->progress),
            'users' => UserResource::collection($this->whenLoaded('users')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
