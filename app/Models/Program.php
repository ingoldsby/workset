<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Program extends Model
{
    use HasFactory;
    use HasUlids;
    use Searchable;
    use SoftDeletes;

    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'visibility',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ProgramVersion::class);
    }

    public function activeVersion(): ?ProgramVersion
    {
        return cache()->remember(
            "program.{$this->id}.active_version",
            now()->addHour(),
            fn () => $this->versions()->where('is_active', true)->first()
        );
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function isPrivate(): bool
    {
        return $this->visibility === 'private';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'owner_name' => $this->owner->name ?? null,
        ];
    }
}
