<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromocodeResource\Pages;
use App\Models\Promocode;
use Brick\Money\Money;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromocodeResource extends Resource
{
    protected static ?string $model = Promocode::class;

    protected static ?string $navigationLabel = 'Promocodes';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Promocode Details')
                ->description('Manage promocode discounts and availability')
                ->aside()
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->label('Code')
                        ->required()
                        ->maxLength(255)
                        ->dehydrateStateUsing(
                            fn (string $state): string => strtoupper(
                                trim($state),
                            ),
                        )
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('discount_percentage')
                        ->label('Discount Percentage')
                        ->numeric()
                        ->minValue(0.01)
                        ->maxValue(100)
                        ->required()
                        ->suffix('%'),

                    Forms\Components\TextInput::make('max_discount_amount')
                        ->label('Max Discount Amount')
                        ->formatStateUsing(
                            fn ($state) => $state instanceof Money
                                ? $state->getAmount()->__toString()
                                : $state,
                        )
                        ->dehydrateStateUsing(fn ($state) => (string) $state)
                        ->numeric()
                        ->required()
                        ->prefix('HUF'),

                    Forms\Components\DateTimePicker::make('starts_at')
                        ->label('Starts At')
                        ->native(false)
                        ->nullable(),

                    Forms\Components\DateTimePicker::make('expires_at')
                        ->label('Expires At')
                        ->native(false)
                        ->nullable()
                        ->after('starts_at'),

                    Forms\Components\TextInput::make('max_global_uses')
                        ->label('Max Global Uses')
                        ->numeric()
                        ->nullable()
                        ->minValue(1)
                        ->helperText('Leave empty for unlimited usage.'),
                ])
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('Discount')
                    ->sortable()
                    ->suffix('%'),

                Tables\Columns\TextColumn::make('max_discount_amount')
                    ->label('Max Discount')
                    ->money(
                        fn (Promocode $record): string => $record->currency ??
                            'HUF',
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('bookings_count')
                    ->label('Uses')
                    ->counts('bookings')
                    ->sortable(),

                Tables\Columns\TextColumn::make('max_global_uses')
                    ->label('Global Cap')
                    ->formatStateUsing(
                        fn ($state): string => $state
                            ? (string) $state
                            : 'Unlimited',
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('active_window')
                    ->label('Window Status')
                    ->getStateUsing(function (Model $record): string {
                        return $record->isActiveAt() ? 'Active' : 'Inactive';
                    })
                    ->badge()
                    ->color(
                        fn (string $state): string => $state === 'Active'
                            ? 'success'
                            : 'danger',
                    ),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([Tables\Filters\TrashedFilter::make()])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromocodes::route('/'),
            'create' => Pages\CreatePromocode::route('/create'),
            'edit' => Pages\EditPromocode::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->withCount('bookings');
    }
}
