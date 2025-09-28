<?php

namespace App\Filament\Resources;

use App\Enums\BookingStatus;
use App\Enums\BookingUrgency;
use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Models\Booking;
use App\Models\Pricing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationLabel = 'Bookings';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Client Information')
                    ->description('Select the client who is requesting this booking')
                    ->aside()
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Client')
                            ->relationship('client.user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(1),

                Forms\Components\Section::make('Service Details')
                    ->description('Choose the service type and pricing tier for this booking')
                    ->aside()
                    ->schema([
                        Forms\Components\Select::make('service_id')
                            ->label('Service')
                            ->relationship('service', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('pricing_id', null)),

                        Forms\Components\Select::make('pricing_id')
                            ->label('Pricing Tier')
                            ->options(function (callable $get) {
                                $serviceId = $get('service_id');
                                if (! $serviceId) {
                                    return [];
                                }

                                return Pricing::where('service_id', $serviceId)
                                    ->get()
                                    ->pluck('amount', 'id')
                                    ->map(fn ($amount) => 'Ft '.number_format($amount, 0));
                            })
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state) {
                                    $pricing = Pricing::find($state);
                                    if ($pricing) {
                                        $set('selected_amount', $pricing->amount);
                                        $set('amount', $pricing->amount); // Base amount before extras
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('selected_amount')
                            ->label('Selected Amount')
                            ->numeric()
                            ->prefix('Ft')
                            ->disabled()
                            ->dehydrated(),
                    ])->columns(1),

                Forms\Components\Section::make('Booking Details')
                    ->description('Set the urgency level, schedule, and calculate the total amount')
                    ->aside()
                    ->schema([
                        Forms\Components\Select::make('urgency')
                            ->label('Urgency')
                            ->options(BookingUrgency::class)
                            ->required()
                            ->native(false),

                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Scheduled Date & Time')
                            ->native(false)
                            ->minDate(now())
                            ->displayFormat('M d, Y H:i'),

                        Forms\Components\Toggle::make('has_cleaning_material')
                            ->label('Client has cleaning materials')
                            ->default(false),

                        Forms\Components\TextInput::make('amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('Ft')
                            ->required(),
                    ])->columns(1),

                Forms\Components\Section::make('Status & Assignment')
                    ->description('Manage the booking status and assign a cleaner')
                    ->aside()
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(BookingStatus::class)
                            ->required()
                            ->native(false)
                            ->default(BookingStatus::Pending),

                        Forms\Components\Select::make('cleaner_id')
                            ->label('Assigned Cleaner')
                            ->relationship('cleaner.user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('client.user.name')
                    ->label('Client')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('service.name')
                    ->label('Service')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('HUF')
                    ->sortable(),

                Tables\Columns\TextColumn::make('urgency')
                    ->label('Urgency')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                Tables\Columns\TextColumn::make('cleaner.user.name')
                    ->label('Assigned Cleaner')
                    ->placeholder('Unassigned')
                    ->searchable(),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Scheduled')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->placeholder('Not scheduled'),

                Tables\Columns\IconColumn::make('has_cleaning_material')
                    ->label('Has Materials')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),

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
                SelectFilter::make('status')
                    ->options(BookingStatus::class)
                    ->native(false),

                SelectFilter::make('urgency')
                    ->options(BookingUrgency::class)
                    ->native(false),

                SelectFilter::make('service')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('cleaner')
                    ->relationship('cleaner.user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('scheduled')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('scheduled_at'))
                    ->label('Scheduled Only'),

                Tables\Filters\Filter::make('unassigned')
                    ->query(fn (Builder $query): Builder => $query->whereNull('cleaner_id'))
                    ->label('Unassigned'),
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
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getWidgets(): array
    {
        return [
            BookingResource\Widgets\BookingStatsWidget::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['client.user', 'service', 'pricing', 'cleaner.user', 'extras']);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ExtrasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'view' => Pages\ViewBooking::route('/{record}'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
