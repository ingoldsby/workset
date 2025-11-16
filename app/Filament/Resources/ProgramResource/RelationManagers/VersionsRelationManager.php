<?php

namespace App\Filament\Resources\ProgramResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'versions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('version_number')
                    ->required()
                    ->numeric()
                    ->default(fn () => $this->getOwnerRecord()->versions()->max('version_number') + 1)
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\Select::make('created_by')
                    ->label('Creator')
                    ->relationship('creator', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->default(auth()->id()),

                Forms\Components\Textarea::make('change_notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active Version')
                    ->default(false)
                    ->helperText('Only one version can be active at a time'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('version_number')
            ->columns([
                Tables\Columns\TextColumn::make('version_number')
                    ->label('Version')
                    ->sortable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('change_notes')
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Ensure only one active version
                        if ($data['is_active'] ?? false) {
                            $this->getOwnerRecord()
                                ->versions()
                                ->update(['is_active' => false]);
                        }

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data, $record): array {
                        // Ensure only one active version
                        if ($data['is_active'] ?? false) {
                            $this->getOwnerRecord()
                                ->versions()
                                ->where('id', '!=', $record->id)
                                ->update(['is_active' => false]);
                        }

                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('version_number', 'desc');
    }
}
