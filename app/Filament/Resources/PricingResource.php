<?php

namespace App\Filament\Resources;

use App\Enums\ServicePricingModel;
use App\Enums\ServiceType;
use App\Filament\Resources\PricingResource\Pages;
use App\Models\Pricing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PricingResource extends Resource
{
    protected static ?string $model = Pricing::class;

    protected static ?string $navigationLabel = 'Pricing';

    protected static ?string $navigationGroup = 'Services';

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Service Selection')
                    ->description('Select the service this pricing tier applies to')
                    ->aside()
                    ->schema([
                        Forms\Components\Select::make('service_id')
                            ->label('Service')
                            ->relationship('service', 'name', fn (Builder $query) => $query
                                ->where('pricing_model', ServicePricingModel::AreaRange->value)
                                ->orWhere('type', ServiceType::Residential->value))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(1),

                Forms\Components\Section::make('Area & Pricing')
                    ->description('Define the area range and corresponding price')
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('min_area')
                            ->label('Minimum Area (sqm)')
                            ->numeric()
                            ->required()
                            ->suffix('sqm')
                            ->placeholder('e.g., 50'),

                        Forms\Components\TextInput::make('max_area')
                            ->label('Maximum Area (sqm)')
                            ->numeric()
                            ->required()
                            ->suffix('sqm')
                            ->placeholder('e.g., 100'),

                        Forms\Components\TextInput::make('amount')
                            ->label('Price')
                            ->numeric()
                            ->prefix('Ft')
                            ->required()
                            ->placeholder('Base price for this area range'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Service')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('area_range')
                    ->label('Area Range')
                    ->getStateUsing(fn (Model $record): string => $record->min_area.'-'.$record->max_area.' sqm')
                    ->searchable(false),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Price')
                    ->money(fn (Pricing $record): string => $record->currency ?? 'HUF')
                    ->sortable(),

                Tables\Columns\TextColumn::make('bookings_count')
                    ->label('Bookings')
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
                Tables\Filters\SelectFilter::make('service')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload(),
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
            ->defaultSort('service_id')
            ->defaultSort('min_area');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('service')
            ->withCount('bookings');
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
            'index' => Pages\ListPricings::route('/'),
            'create' => Pages\CreatePricing::route('/create'),
            'edit' => Pages\EditPricing::route('/{record}/edit'),
        ];
    }
}
