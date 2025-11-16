<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsSnapshot extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'user_id',
        'snapshot_type',
        'snapshot_date',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'data' => 'array',
        ];
    }
}
