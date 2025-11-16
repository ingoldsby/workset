<?php

namespace App\Filament\Resources;

use App\Enums\EquipmentType;
use App\Enums\ExerciseCategory;
use App\Enums\ExerciseLevel;
use App\Enums\ExerciseMechanics;
use App\Enums\MuscleGroup;
use App\Filament\Resources\ExerciseResource\Pages;
use App\Models\Exercise;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExerciseResource extends Resource
{
    protected static ?string $model = Exercise::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Exercise Library';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('category')
                            ->options(collect(ExerciseCategory::cases())->mapWithKeys(
                                fn ($case) => [$case->value => $case->label()]
                            ))
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('level')
                            ->options(collect(ExerciseLevel::cases())->mapWithKeys(
                                fn ($case) => [$case->value => $case->label()]
                            ))
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('mechanics')
                            ->options(collect(ExerciseMechanics::cases())->mapWithKeys(
                                fn ($case) => [$case->value => $case->label()]
                            ))
                            ->native(false),

                        Forms\Components\TextInput::make('language')
                            ->default('en-AU')
                            ->maxLength(10),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Muscle Groups')
                    ->schema([
                        Forms\Components\Select::make('primary_muscle')
                            ->options(collect(MuscleGroup::cases())->mapWithKeys(
                                fn ($case) => [$case->value => $case->label()]
                            ))
                            ->required()
                            ->native(false)
                            ->searchable(),

                        Forms\Components\Select::make('secondary_muscles')
                            ->options(collect(MuscleGroup::cases())->mapWithKeys(
                                fn ($case) => [$case->value => $case->label()]
                            ))
                            ->multiple()
                            ->native(false)
                            ->searchable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Equipment')
                    ->schema([
                        Forms\Components\Select::make('equipment')
                            ->options(collect(EquipmentType::cases())->mapWithKeys(
                                fn ($case) => [$case->value => $case->label()]
                            ))
                            ->required()
                            ->native(false)
                            ->searchable(),

                        Forms\Components\Select::make('equipment_variants')
                            ->label('Equipment Variants')
                            ->options(collect(EquipmentType::cases())->mapWithKeys(
                                fn ($case) => [$case->value => $case->label()]
                            ))
                            ->multiple()
                            ->native(false)
                            ->searchable()
                            ->helperText('Alternative equipment options for this exercise'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\TagsInput::make('aliases')
                            ->helperText('Alternative names for this exercise')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('wger_id')
                            ->label('wger API ID')
                            ->numeric()
                            ->helperText('Reference ID from wger exercise database'),
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

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn (ExerciseCategory $state): string => $state->label())
                    ->sortable(),

                Tables\Columns\TextColumn::make('primary_muscle')
                    ->label('Primary Muscle')
                    ->formatStateUsing(fn (MuscleGroup $state): string => $state->label())
                    ->sortable(),

                Tables\Columns\TextColumn::make('equipment')
                    ->formatStateUsing(fn (EquipmentType $state): string => $state->label())
                    ->sortable(),

                Tables\Columns\TextColumn::make('mechanics')
                    ->formatStateUsing(fn (?ExerciseMechanics $state): string => $state?->label() ?? '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('level')
                    ->badge()
                    ->color(fn (ExerciseLevel $state): string => match ($state) {
                        ExerciseLevel::Beginner => 'success',
                        ExerciseLevel::Intermediate => 'warning',
                        ExerciseLevel::Advanced => 'danger',
                    })
                    ->formatStateUsing(fn (ExerciseLevel $state): string => $state->label())
                    ->sortable(),

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
                Tables\Filters\SelectFilter::make('category')
                    ->options(collect(ExerciseCategory::cases())->mapWithKeys(
                        fn ($case) => [$case->value => $case->label()]
                    ))
                    ->native(false),

                Tables\Filters\SelectFilter::make('primary_muscle')
                    ->label('Primary Muscle')
                    ->options(collect(MuscleGroup::cases())->mapWithKeys(
                        fn ($case) => [$case->value => $case->label()]
                    ))
                    ->native(false),

                Tables\Filters\SelectFilter::make('equipment')
                    ->options(collect(EquipmentType::cases())->mapWithKeys(
                        fn ($case) => [$case->value => $case->label()]
                    ))
                    ->native(false),

                Tables\Filters\SelectFilter::make('level')
                    ->options(collect(ExerciseLevel::cases())->mapWithKeys(
                        fn ($case) => [$case->value => $case->label()]
                    ))
                    ->native(false),

                Tables\Filters\SelectFilter::make('mechanics')
                    ->options(collect(ExerciseMechanics::cases())->mapWithKeys(
                        fn ($case) => [$case->value => $case->label()]
                    ))
                    ->native(false),
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
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListExercises::route('/'),
            'create' => Pages\CreateExercise::route('/create'),
            'view' => Pages\ViewExercise::route('/{record}'),
            'edit' => Pages\EditExercise::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
