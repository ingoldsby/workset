<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PtAssignmentResource\Pages;
use App\Models\PtAssignment;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PtAssignmentResource extends Resource
{
    protected static ?string $model = PtAssignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'PT Assignments';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Assignment Details')
                    ->schema([
                        Forms\Components\Select::make('pt_id')
                            ->label('Personal Trainer')
                            ->options(User::query()
                                ->where('role', 'pt')
                                ->orWhere('role', 'admin')
                                ->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('member_id')
                            ->label('Member')
                            ->options(User::query()
                                ->where('role', 'member')
                                ->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\DateTimePicker::make('assigned_at')
                            ->required()
                            ->default(now())
                            ->native(false),

                        Forms\Components\DateTimePicker::make('unassigned_at')
                            ->label('Unassigned At')
                            ->native(false)
                            ->helperText('Leave blank for active assignments'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pt.name')
                    ->label('Personal Trainer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('member.name')
                    ->label('Member')
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
                Tables\Filters\SelectFilter::make('pt_id')
                    ->label('Personal Trainer')
                    ->options(User::query()
                        ->where('role', 'pt')
                        ->orWhere('role', 'admin')
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('member_id')
                    ->label('Member')
                    ->options(User::query()
                        ->where('role', 'member')
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->queries(
                        true: fn ($query) => $query->whereNull('unassigned_at'),
                        false: fn ($query) => $query->whereNotNull('unassigned_at'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPtAssignments::route('/'),
            'create' => Pages\CreatePtAssignment::route('/create'),
            'view' => Pages\ViewPtAssignment::route('/{record}'),
            'edit' => Pages\EditPtAssignment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['pt', 'member']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNull('unassigned_at')->count();
    }
}
