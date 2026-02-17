<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistrictResource\Pages;
use App\Models\District;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DistrictResource extends Resource
{
    protected static ?string $model = District::class;

    protected static ?string $navigationLabel = 'Districts';

    protected static ?string $navigationGroup = 'Locations';

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('District Information')
                ->description('Define the district name and associated city')
                ->aside()
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('District Name')
                        ->required()
                        ->placeholder('e.g., District V, Belváros')
                        ->translatable(),

                    Forms\Components\Select::make('city_id')
                        ->label('City')
                        ->relationship('city', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->columns(1),

            // Forms\Components\Section::make('Geographic Boundaries')
            //     ->description('Define the geographic boundaries for service area mapping')
            //     ->aside()
            //     ->schema([
            //         Forms\Components\Textarea::make('boundaries')
            //             ->label('Boundary Coordinates')
            //             ->placeholder('Geographic boundary data (Polygon format)')
            //             ->helperText('This field is used for geographic service area calculations')
            //             ->columnSpanFull(),
            //     ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('District')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('city.name')
                    ->label('City')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('has_boundaries')
                    ->label('Has Boundaries')
                    ->getStateUsing(
                        fn (Model $record): bool => ! is_null(
                            $record->boundaries,
                        ),
                    )
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('warning'),

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
                Tables\Filters\SelectFilter::make('city')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('has_boundaries')
                    ->query(
                        fn (Builder $query): Builder => $query->whereNotNull(
                            'boundaries',
                        ),
                    )
                    ->label('With Geographic Boundaries'),

                Tables\Filters\Filter::make('missing_boundaries')
                    ->query(
                        fn (Builder $query): Builder => $query->whereNull(
                            'boundaries',
                        ),
                    )
                    ->label('Missing Boundaries'),
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
            ->defaultSort('city_id')
            ->defaultSort('name');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('city');
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
            'index' => Pages\ListDistricts::route('/'),
            'create' => Pages\CreateDistrict::route('/create'),
            'edit' => Pages\EditDistrict::route('/{record}/edit'),
        ];
    }
}
