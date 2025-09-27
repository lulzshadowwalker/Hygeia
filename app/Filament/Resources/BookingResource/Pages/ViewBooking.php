<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Enums\BookingStatus;
use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('confirm')
                ->label('Confirm Booking')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === BookingStatus::Pending)
                ->action(function () {
                    $this->record->update(['status' => BookingStatus::Confirmed]);

                    Notification::make()
                        ->title('Booking Confirmed')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status']);
                }),

            Actions\Action::make('complete')
                ->label('Mark as Completed')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status === BookingStatus::Confirmed)
                ->action(function () {
                    $this->record->update(['status' => BookingStatus::Completed]);

                    Notification::make()
                        ->title('Booking Completed')
                        ->success()
                        ->send();

                    $this->refreshFormData(['status']);
                }),

            Actions\Action::make('cancel')
                ->label('Cancel Booking')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->record->status, [BookingStatus::Pending, BookingStatus::Confirmed]))
                ->action(function () {
                    $this->record->update(['status' => BookingStatus::Cancelled]);

                    Notification::make()
                        ->title('Booking Cancelled')
                        ->warning()
                        ->send();

                    $this->refreshFormData(['status']);
                }),
        ];
    }
}
