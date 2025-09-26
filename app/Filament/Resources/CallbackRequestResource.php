<?php

namespace App\Filament\Resources;

use App\Enums\CallbackRequestStatus;
use App\Filament\Resources\CallbackRequestResource\Pages;
use App\Models\CallbackRequest;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CallbackRequestResource extends Resource
{
    protected static ?string $model = CallbackRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-phone';

    public static function getNavigationLabel(): string
    {
        return 'Callback Requests';
    }

    public static function getLabel(): string
    {
        return 'Callback Request';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Callback Requests';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'user.name',
            'user.email',
            'user.phone',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Phone' => $record->user?->phone ?? 'Unavailable',
            'Email' => $record->user?->email ?? 'Unavailable',
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Support';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', CallbackRequestStatus::Pending)->count() ?: null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $count = static::getModel()::where('status', CallbackRequestStatus::Pending)->count();
        if ($count === 0) {
            return null;
        }

        return "{$count} pending callback request".($count > 1 ? 's' : '');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Details')
                    ->description('Manage the details of the callback request.')
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('User')
                            ->disabled()
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options(CallbackRequestStatus::class)
                            ->required()
                            ->searchable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Phone')
                    ->default('Unavailable')
                    ->url(fn (CallbackRequest $record): ?string => $record->user?->phone ? "tel:{$record->user->phone}" : null)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->url(fn (CallbackRequest $record): ?string => $record->user?->email ? "mailto:{$record->user->email}" : null)
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Requested At')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Last Updated')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver(),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('mark_as_completed')
                        ->label('Mark as Completed')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (CallbackRequest $record) => $record->update(['status' => CallbackRequestStatus::Completed]))
                        ->visible(fn (CallbackRequest $record) => $record->status !== CallbackRequestStatus::Completed),

                    Tables\Actions\Action::make('mark_as_failed')
                        ->label('Mark as Failed')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn (CallbackRequest $record) => $record->update(['status' => CallbackRequestStatus::Failed]))
                        ->visible(fn (CallbackRequest $record) => $record->status !== CallbackRequestStatus::Failed),

                    Tables\Actions\Action::make('mark_as_pending')
                        ->label('Mark as Pending')
                        ->icon('heroicon-o-clock')
                        ->color('warning')
                        ->action(fn (CallbackRequest $record) => $record->update(['status' => CallbackRequestStatus::Pending]))
                        ->visible(fn (CallbackRequest $record) => $record->status !== CallbackRequestStatus::Pending),
                ]),
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
            'index' => Pages\ListCallbackRequests::route('/'),
        ];
    }
}
