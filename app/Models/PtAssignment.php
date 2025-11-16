<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PtAssignment extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'pt_id',
        'member_id',
        'assigned_at',
        'unassigned_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'unassigned_at' => 'datetime',
        ];
    }

    public function pt(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pt_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function isActive(): bool
    {
        return $this->unassigned_at === null;
    }
}
