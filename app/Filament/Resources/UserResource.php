<?php

namespace App\Filament\Resources;

use App\Enums\Role;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('role')
                            ->options([
                                Role::Admin->value => Role::Admin->label(),
                                Role::PT->value => Role::PT->label(),
                                Role::Member->value => Role::Member->label(),
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255),

                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Preferences')
                    ->schema([
                        Forms\Components\TextInput::make('timezone')
                            ->default('Australia/Brisbane')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('locale')
                            ->default('en-AU')
                            ->maxLength(10),

                        Forms\Components\TextInput::make('weight_unit')
                            ->default('kg')
                            ->maxLength(10),

                        Forms\Components\TextInput::make('distance_unit')
                            ->default('km')
                            ->maxLength(10),

                        Forms\Components\TextInput::make('weight_rounding')
                            ->numeric()
                            ->default(0.5)
                            ->step(0.1),

                        Forms\Components\TextInput::make('barbell_weight')
                            ->numeric()
                            ->default(15)
                            ->step(0.5),

                        Forms\Components\Toggle::make('show_pace_speed')
                            ->default(false),

                        Forms\Components\Toggle::make('dumbbell_pair_mode')
                            ->default(true),

                        Forms\Components\Toggle::make('completed_onboarding')
                            ->default(false),

                        Forms\Components\DateTimePicker::make('onboarded_at')
                            ->native(false),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

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

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\IconColumn::make('completed_onboarding')
                    ->label('Onboarded')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        Role::Admin->value => Role::Admin->label(),
                        Role::PT->value => Role::PT->label(),
                        Role::Member->value => Role::Member->label(),
                    ])
                    ->native(false),

                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email Verified')
                    ->nullable(),

                Tables\Filters\TernaryFilter::make('completed_onboarding')
                    ->label('Completed Onboarding'),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PtAssignmentsRelationManager::class,
            RelationManagers\InvitesSentRelationManager::class,
            RelationManagers\ProgramsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
