<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PtAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'ptAssignments';

    protected static ?string $title = 'PT Assignments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pt_id')
                    ->label('Personal Trainer')
                    ->relationship('pt', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\DateTimePicker::make('assigned_at')
                    ->required()
                    ->default(now())
                    ->native(false),

                Forms\Components\DateTimePicker::make('unassigned_at')
                    ->native(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('pt.name')
                    ->label('Personal Trainer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('assigned_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('unassigned_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Active'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->isActive())
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->queries(
                        true: fn ($query) => $query->whereNull('unassigned_at'),
                        false: fn ($query) => $query->whereNotNull('unassigned_at'),
                    ),
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
            ->defaultSort('assigned_at', 'desc');
    }
}
