<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InvitesSentRelationManager extends RelationManager
{
    protected static string $relationship = 'invitesSent';

    protected static ?string $title = 'Invites Sent';

    public function form(Form $form): Form
    {
        return $form
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
                    ->native(false),

                Forms\Components\Select::make('pt_id')
                    ->label('Assign to PT')
                    ->relationship('personalTrainer', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\DateTimePicker::make('expires_at')
                    ->required()
                    ->default(now()->addDays(30))
                    ->native(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
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
                    ->formatStateUsing(fn (Role $state): string => $state->label()),

                Tables\Columns\TextColumn::make('personalTrainer.name')
                    ->label('Assigned PT')
                    ->placeholder('None'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('accepted_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Pending'),

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
                    }),
            ])
            ->filters([
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
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
