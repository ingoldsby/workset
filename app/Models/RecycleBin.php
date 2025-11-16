<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RecycleBin extends Model
{
    use HasFactory;
    use HasUlids;

    protected $table = 'recycle_bin';

    protected $fillable = [
        'user_id',
        'recyclable_type',
        'recyclable_id',
        'data',
        'deleted_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'deleted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function recyclable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
