<?php

namespace App\Filament\Resources\BookingResource\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalBookings = Booking::count();
        $pendingBookings = Booking::where('status', BookingStatus::Pending)->count();
        $completedBookings = Booking::where('status', BookingStatus::Completed)->count();
        $totalRevenue = Booking::where('status', BookingStatus::Completed)->sum('amount');
        $unassignedBookings = Booking::whereNull('cleaner_id')->count();

        return [
            Stat::make('Total Bookings', $totalBookings)
                ->description('All bookings in the system')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Pending Bookings', $pendingBookings)
                ->description('Awaiting confirmation')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Completed Bookings', $completedBookings)
                ->description('Successfully completed')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total Revenue', '$'.number_format($totalRevenue, 2))
                ->description('From completed bookings')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Unassigned', $unassignedBookings)
                ->description('Need cleaner assignment')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
