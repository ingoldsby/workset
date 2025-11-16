<?php

namespace App\Models;

use App\Enums\Role;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory;
    use HasUlids;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'timezone',
        'locale',
        'weight_unit',
        'distance_unit',
        'weight_rounding',
        'barbell_weight',
        'show_pace_speed',
        'dumbbell_pair_mode',
        'completed_onboarding',
        'onboarded_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'weight_rounding' => 'decimal:2',
            'barbell_weight' => 'decimal:2',
            'show_pace_speed' => 'boolean',
            'dumbbell_pair_mode' => 'boolean',
            'completed_onboarding' => 'boolean',
            'onboarded_at' => 'datetime',
        ];
    }

    public function ptAssignments(): HasMany
    {
        return $this->hasMany(PtAssignment::class, 'member_id');
    }

    public function memberAssignments(): HasMany
    {
        return $this->hasMany(PtAssignment::class, 'pt_id');
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class, 'owner_id');
    }

    public function memberExercises(): HasMany
    {
        return $this->hasMany(MemberExercise::class, 'user_id');
    }

    public function trainingSessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class, 'user_id');
    }

    public function sessionPlans(): HasMany
    {
        return $this->hasMany(SessionPlan::class, 'user_id');
    }

    public function cardioEntries(): HasMany
    {
        return $this->hasMany(CardioEntry::class, 'user_id');
    }

    public function invitesSent(): HasMany
    {
        return $this->hasMany(Invite::class, 'inviter_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isPt(): bool
    {
        return $this->role === Role::PT;
    }

    public function isMember(): bool
    {
        return $this->role === Role::Member;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }
}
