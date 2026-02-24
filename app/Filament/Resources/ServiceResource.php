<?php

namespace App\Filament\Resources;

use App\Enums\ServiceType;
use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Brick\Money\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationLabel = 'Services';

    protected static ?string $navigationGroup = 'Services';

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Service Information')
                    ->description('Define the service details and type')
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Service Name')
                            ->required()
                            ->placeholder('e.g., Home Cleaning, Office Cleaning')
                            ->translatable(),

                        Forms\Components\Select::make('type')
                            ->label('Service Type')
                            ->options(ServiceType::class)
                            ->required()
                            ->native(false)
                            ->default(ServiceType::Residential)
                            ->live()
                            ->afterStateHydrated(function ($state, callable $set) {
                                $type = $state?->value ?? ServiceType::Residential->value;
                                $set('type', $type);
                            }),

                        Forms\Components\TextInput::make('price_per_meter')
                            ->label('Price per Meter')
                            ->numeric()
                            ->prefix('Ft')
                            ->visible(fn (Forms\Get $get) => $get('type') === ServiceType::Residential->value)
                            ->required(fn (Forms\Get $get) => $get('type') === ServiceType::Residential->value),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Service Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->getStateUsing(fn (Service $record) => $record->type)
                    ->formatStateUsing(fn ($state): string => $state->getLabel())
                    ->color(fn ($state): string => $state->getColor())
                    ->icon(fn ($state): string => $state->getIcon()),

                Tables\Columns\TextColumn::make('pricings_count')
                    ->label('Pricing Tiers')
                    ->counts('pricings')
                    ->sortable()
                    ->formatStateUsing(fn ($state, Service $record) => $record->type === ServiceType::Residential ? 'N/A' : $state),

                Tables\Columns\TextColumn::make('price_per_meter')
                    ->label('Pricing Per Meter')
                    ->sortable()
                    ->formatStateUsing(function ($state, Service $record): string {
                        if ($record->type !== ServiceType::Residential || $state === null) {
                            return 'N/A';
                        }

                        if ($state instanceof Money) {
                            return $state->getAmount()->toScale(2).' '.($record->currency ?? 'HUF');
                        }

                        return number_format((float) $state, 2).' '.($record->currency ?? 'HUF');
                    }),

                Tables\Columns\TextColumn::make('bookings_count')
                    ->label('Total Bookings')
                    ->counts('bookings')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(ServiceType::class)
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['pricings', 'bookings']);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PricingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
