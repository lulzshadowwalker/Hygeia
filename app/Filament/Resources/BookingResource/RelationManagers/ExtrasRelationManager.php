<?php

namespace App\Filament\Resources\BookingResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ExtrasRelationManager extends RelationManager
{
    protected static string $relationship = 'extras';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('amount')
                ->numeric()
                ->prefix('HUF')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Extra Service')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Price')
                    ->money(fn ($record): string => $record->currency ?? 'HUF')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pivot.amount')
                    ->label('Applied Amount')
                    ->money(
                        fn ($record): string => $record->pivot->currency ??
                            ($record->currency ?? 'HUF'),
                    )
                    ->placeholder('Same as base price'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()->form(
                    fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Custom Amount (optional)')
                            ->numeric()
                            ->prefix('HUF')
                            ->placeholder('Leave empty to use base price'),
                    ],
                ),
            ])
            ->actions([Tables\Actions\DetachAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
