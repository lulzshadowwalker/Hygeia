<?php

namespace App\Filament\Resources;

use App\Enums\SupportTicketStatus;
use App\Filament\Exports\SupportTicketExporter;
use App\Filament\Resources\SupportTicketResource\Pages;
use App\Filament\Resources\SupportTicketResource\Widgets\SupportTicketsStatsWidget;
use App\Models\SupportTicket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    public static function getNavigationLabel(): string
    {
        return 'Support Tickets';
    }

    public static function getLabel(): string
    {
        return 'Support Tickets';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Support Ticket';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'user.name',
            'user.email',
            'message',
            'subject',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Attached Information' => $record->name . ' ' . $record->phone,
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Support';
    }

    public static function getNavigationBadge(): ?string
    {
        return SupportTicket::open()->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        $count = SupportTicket::open()->count();
        if ($count === 0) {
            return 'No open tickets';
        } elseif ($count === 1) {
            return 'One open ticket';
        }
        return "{$count} open tickets";
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ticket Information')
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('number')
                            ->label('Number')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->formatStateUsing(fn($state) => Str::replace('TICKET-', '', $state)),

                        Forms\Components\TextInput::make('user.name')
                            ->label('Name')
                            ->disabled(),

                        Forms\Components\Textarea::make('message')
                            ->label('Message')
                            ->required()
                            ->columnSpanFull()
                            ->rows(8)
                            ->disabled(),

                        Forms\Components\TextInput::make('subject')
                            ->label('Subject')
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options(Arr::collapse(Arr::map(SupportTicketStatus::cases(), fn($status) => [$status->value => $status->label()])))
                            ->searchable(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->description(fn($record) => $record->user->email)
                    ->sortable()
                    ->searchable(
                        query: function (Builder $query, $search) {
                            $query->where('user.name', 'like', "%{$search}%")
                                ->orWhere('user.email', 'like', "%{$search}%");
                        }
                    ),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Subject')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->subject),

                Tables\Columns\TextColumn::make('user_type')
                    ->label('Author Type')
                    ->getStateUsing(fn($record) => $record->user->isCleaner ? 'Cleaner' : 'Client')
                    ->color(fn($record) => $record->user->isCleaner ? 'primary' : 'secondary')
                    ->badge()
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Sent At')
                    ->getStateUsing(fn($record) => $record->created_at->diffForHumans())
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state->label())
                    ->sortable(),

                Tables\Columns\TextColumn::make('number')
                    ->label('Number')
                    ->badge()
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => Str::replace('TICKET-', '', $state))
                    ->searchable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\ViewAction::make()
                    ->slideOver(),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('open')
                        ->label('Open')
                        ->color(SupportTicketStatus::Open->getColor())
                        ->icon(SupportTicketStatus::Open->getIcon())
                        ->visible(fn($record) => !$record->isOpen)
                        ->action(fn($record) => $record->markAsOpen()),

                    Tables\Actions\Action::make('in-progress')
                        ->label('In Progress')
                        ->visible(fn($record) => !$record->isInProgress)
                        ->action(fn($record) => $record->markAsInProgress()),

                    Tables\Actions\Action::make('resolve')
                        ->label('Resolve')
                        ->color(SupportTicketStatus::Resolved->getColor())
                        ->icon(SupportTicketStatus::Resolved->getIcon())
                        ->visible(fn($record) => !$record->isResolved)
                        ->action(fn($record) => $record->markAsResolved()),

                ])
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
            'index' => Pages\ListSupportTickets::route('/'),
            'edit' => Pages\EditSupportTicket::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            SupportTicketsStatsWidget::class,
        ];
    }
}
