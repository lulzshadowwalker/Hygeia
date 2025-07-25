<?php

// CleanerResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\CleanerResource\Pages;
use App\Filament\Resources\CleanerResource\RelationManagers;
use App\Models\Cleaner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use App\Enums\UserStatus;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CleanerResource extends Resource
{
    protected static ?string $model = Cleaner::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->description('Manage user account details')
                    ->aside()
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $user = \App\Models\User::find($state);
                                    if ($user) {
                                        $set('user_email_display', $user->email);
                                        $set('user_username_display', $user->username);
                                        $set('user_status', $user->status);
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('user_email_display')
                            ->label('Email')
                            ->disabled(),

                        Forms\Components\TextInput::make('user_username_display')
                            ->label('Username')
                            ->disabled(),

                        Forms\Components\Select::make('user_status')
                            ->label('Status')
                            ->options(UserStatus::class)
                            ->required()
                            ->dehydrated(false),
                    ]),

                Forms\Components\Section::make('Cleaner Details')
                    ->description('Manage cleaner-specific information')
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('service_area')
                            ->required()
                            ->placeholder('Enter service area'),

                        Forms\Components\TextInput::make('max_hours_per_week')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('Enter max hours per week'),

                        Forms\Components\TextInput::make('years_of_experience')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('Enter years of experience'),

                        Forms\Components\TextInput::make('service_radius')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('Enter service radius in km'),

                        Forms\Components\Toggle::make('has_cleaning_supplies')
                            ->label('Has Cleaning Supplies'),

                        Forms\Components\Toggle::make('comfortable_with_pets')
                            ->label('Comfortable with Pets'),

                        Forms\Components\Toggle::make('agreed_to_terms')
                            ->label('Agreed to Terms')
                            ->required(),
                    ]),

                Forms\Components\Section::make('Media')
                    ->description('Manage cleaner identification media')
                    ->aside()
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('id_card')
                            ->collection(Cleaner::MEDIA_COLLECTION_ID_CARD)
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['16:9', '4:3', '1:1'])
                            ->maxSize(10240)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cleaner Name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('user.username')
                    ->label('Username')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('service_area')
                    ->sortable()
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('years_of_experience')
                    ->numeric()
                    ->sortable()
                    ->suffix(' years'),

                Tables\Columns\IconColumn::make('has_cleaning_supplies')
                    ->boolean()
                    ->label('Supplies'),

                Tables\Columns\IconColumn::make('comfortable_with_pets')
                    ->boolean()
                    ->label('Pet-Friendly'),

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
                // Tables\Filters\SelectFilter::make('user.status')
                //     ->relationship('user', 'status')
                //     ->label('Status')
                //     ->options(UserStatus::class)
                //     ->multiple()
                //     ->preload(),

                Tables\Filters\TernaryFilter::make('has_cleaning_supplies')
                    ->label('Has Cleaning Supplies')
                    ->boolean(),

                Tables\Filters\TernaryFilter::make('comfortable_with_pets')
                    ->label('Comfortable with Pets')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListCleaners::route('/'),
            'create' => Pages\CreateCleaner::route('/create'),
            'edit' => Pages\EditCleaner::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->user->name . ' (' . $record->user->status->getLabel() . ')';
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name', 'service_area'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Status' => $record->user->status->getLabel(),
            'Service Area' => $record->service_area,
            'Experience' => $record->years_of_experience . ' years',
            'Created' => $record->created_at->diffForHumans(),
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl('edit', ['record' => $record]);
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'created_at';
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }
}
