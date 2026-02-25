<?php

namespace App\Filament\Resources\ServiceResource\RelationManagers;

use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PricingsRelationManager extends RelationManager
{
    protected static string $relationship = 'pricings';

    protected static ?string $recordTitleAttribute = 'amount';

    public static function canViewForRecord(
        Model $ownerRecord,
        string $pageClass,
    ): bool {
        if (! $ownerRecord instanceof Service) {
            return false;
        }

        return $ownerRecord->usesAreaRangePricing();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Pricing Details')
                ->description(
                    'Define the area range and price for this service',
                )
                ->schema([
                    Forms\Components\TextInput::make('min_area')
                        ->label('Minimum Area (sqm)')
                        ->numeric()
                        ->required()
                        ->suffix('sqm'),

                    Forms\Components\TextInput::make('max_area')
                        ->label('Maximum Area (sqm)')
                        ->numeric()
                        ->required()
                        ->suffix('sqm'),

                    Forms\Components\TextInput::make('amount')
                        ->label('Price')
                        ->numeric()
                        ->prefix('HUF')
                        ->required(),
                ])
                ->columns(3),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                Tables\Columns\TextColumn::make('min_area')
                    ->label('Min Area')
                    ->suffix(' sqm')
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_area')
                    ->label('Max Area')
                    ->suffix(' sqm')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Price')
                    ->money(fn ($record): string => $record->currency ?? 'HUF')
                    ->sortable(),

                Tables\Columns\TextColumn::make('bookings_count')
                    ->label('Bookings')
                    ->counts('bookings')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('min_area');
    }

    public function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('bookings');
    }
}
