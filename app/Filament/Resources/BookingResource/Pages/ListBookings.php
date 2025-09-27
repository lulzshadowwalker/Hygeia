<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Enums\BookingStatus;
use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BookingResource\Widgets\BookingStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Bookings')
                ->badge(Booking::count()),

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn ($query) => $query->where('status', BookingStatus::Pending))
                ->badge(Booking::where('status', BookingStatus::Pending)->count())
                ->badgeColor('warning'),

            'confirmed' => Tab::make('Confirmed')
                ->modifyQueryUsing(fn ($query) => $query->where('status', BookingStatus::Confirmed))
                ->badge(Booking::where('status', BookingStatus::Confirmed)->count())
                ->badgeColor('info'),

            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn ($query) => $query->where('status', BookingStatus::Completed))
                ->badge(Booking::where('status', BookingStatus::Completed)->count())
                ->badgeColor('success'),

            'unassigned' => Tab::make('Unassigned')
                ->modifyQueryUsing(fn ($query) => $query->whereNull('cleaner_id'))
                ->badge(Booking::whereNull('cleaner_id')->count())
                ->badgeColor('danger'),
        ];
    }
}
