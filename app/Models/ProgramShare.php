<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProgramShare extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'program_id',
        'shared_by_id',
        'share_token',
        'is_active',
        'expires_at',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
            'view_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $share): void {
            if (! $share->share_token) {
                $share->share_token = Str::random(64);
            }
        });
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function sharedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return $this->is_active && ! $this->isExpired();
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }
}
