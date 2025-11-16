<?php

namespace App\Filament\Resources;

use App\Enums\Role;
use App\Filament\Resources\InviteResource\Pages;
use App\Models\Invite;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class InviteResource extends Resource
{
    protected static ?string $model = Invite::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Invite Details')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('role')
                            ->options([
                                Role::PT->value => Role::PT->label(),
                                Role::Member->value => Role::Member->label(),
                            ])
                            ->required()
                            ->default(Role::Member->value)
                            ->native(false),

                        Forms\Components\Select::make('invited_by')
                            ->label('Invited By')
                            ->relationship('inviter', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(auth()->id()),

                        Forms\Components\Select::make('pt_id')
                            ->label('Assign to PT')
                            ->options(User::query()
                                ->where('role', 'pt')
                                ->orWhere('role', 'admin')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->helperText('Optionally assign this member to a PT'),

                        Forms\Components\TextInput::make('token')
                            ->default(fn () => Str::random(32))
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->required()
                            ->default(now()->addDays(30))
                            ->native(false)
                            ->minDate(now()),

                        Forms\Components\DateTimePicker::make('accepted_at')
                            ->native(false)
                            ->disabled()
                            ->helperText('Automatically set when invite is accepted'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (Role $state): string => match ($state) {
                        Role::Admin => 'danger',
                        Role::PT => 'warning',
                        Role::Member => 'success',
                    })
                    ->formatStateUsing(fn (Role $state): string => $state->label())
                    ->sortable(),

                Tables\Columns\TextColumn::make('inviter.name')
                    ->label('Invited By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('personalTrainer.name')
                    ->label('Assigned PT')
                    ->placeholder('None')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if ($record->isAccepted()) {
                            return 'Accepted';
                        }

                        if ($record->isExpired()) {
                            return 'Expired';
                        }

                        return 'Pending';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Accepted' => 'success',
                        'Expired' => 'danger',
                        'Pending' => 'warning',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('accepted_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Pending')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        Role::PT->value => Role::PT->label(),
                        Role::Member->value => Role::Member->label(),
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('invited_by')
                    ->label('Invited By')
                    ->relationship('inviter', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'expired' => 'Expired',
                    ])
                    ->query(function ($query, $state) {
                        if (! $state['value']) {
                            return $query;
                        }

                        return match ($state['value']) {
                            'pending' => $query->whereNull('accepted_at')->where('expires_at', '>', now()),
                            'accepted' => $query->whereNotNull('accepted_at'),
                            'expired' => $query->whereNull('accepted_at')->where('expires_at', '<=', now()),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('copy_link')
                    ->label('Copy Link')
                    ->icon('heroicon-o-clipboard')
                    ->color('gray')
                    ->action(function ($record) {
                        // This would copy the invite link to clipboard
                        // Actual implementation would require JS
                    })
                    ->visible(fn ($record) => $record->isPending()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvites::route('/'),
            'create' => Pages\CreateInvite::route('/create'),
            'view' => Pages\ViewInvite::route('/{record}'),
            'edit' => Pages\EditInvite::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['inviter', 'personalTrainer']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
