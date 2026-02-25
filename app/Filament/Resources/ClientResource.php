<?php

namespace App\Filament\Resources;

use App\Enums\UserStatus;
use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Client Details')
                    ->description('Manage client information')
                    ->aside()
                    ->schema([
                        // Forms\Components\Select::make('user_id')
                        //     ->relationship('user', 'name')
                        //     ->required()
                        //     ->preload()
                        //     ->searchable()
                        //     ->reactive()
                        //     ->afterStateUpdated(function ($state, callable $set) {
                        //         if ($state) {
                        //             $user = \App\Models\User::find($state);
                        //             if ($user) {
                        //                 $set('user_email_display', $user->email);
                        //                 $set('user_username_display', $user->username);
                        //                 $set('user_status', $user->status);
                        //             }
                        //         }
                        //     }),

                        // Forms\Components\TextInput::make('user_email_display')
                        //     ->label('Email')
                        //     ->disabled()
                        //     ->dehydrated(false),

                        // Forms\Components\TextInput::make('user_username_display')
                        //     ->label('Username')
                        //     ->disabled()
                        //     ->dehydrated(false),

                        // Forms\Components\Select::make('user_status')
                        //     ->label('Status')
                        //     ->options(UserStatus::class)
                        //     ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->label('User ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Client Name')
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
                    ->badge()
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
                // Tables\Filters\SelectFilter::make('user.status')
                //     ->relationship('user', 'status')
                //     ->label('Status')
                //     ->options(UserStatus::class)
                //     ->multiple()
                //     ->preload(),
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->user->name.' ('.$record->user->status->getLabel().')';
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Status' => $record->user->status->getLabel(),
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
