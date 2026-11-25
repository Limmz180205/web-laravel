<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'year',
        'score',
        'genres',
        'status',
        'total_units',
        'creator',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'score' => 'float',
            'genres' => 'array',
            'total_units' => 'integer',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('progress')
            ->withTimestamps();
    }
}
