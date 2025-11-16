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
        'is_template',
        'category',
        'tags',
        'install_count',
        'cloned_from_id',
    ];

    protected function casts(): array
    {
        return [
            'is_template' => 'boolean',
            'install_count' => 'integer',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ProgramVersion::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(ProgramShare::class);
    }

    public function clonedFrom(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'cloned_from_id');
    }

    public function clones(): HasMany
    {
        return $this->hasMany(Program::class, 'cloned_from_id');
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

    public function isTemplate(): bool
    {
        return $this->is_template;
    }

    public function getTagsArray(): array
    {
        if (! $this->tags) {
            return [];
        }

        return json_decode($this->tags, true) ?? [];
    }

    public function setTagsArray(array $tags): void
    {
        $this->tags = json_encode($tags);
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
