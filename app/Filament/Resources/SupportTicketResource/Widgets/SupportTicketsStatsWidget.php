<?php

namespace App\Filament\Resources\SupportTicketResource\Widgets;

use App\Models\SupportTicket;
use Filament\Notifications\Notification;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SupportTicketsStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '3s';

    protected function getStats(): array
    {
        // Fetch the number of tickets for the last 30 days and the previous 30 days for comparison
        $currentPeriodTickets = SupportTicket::where('created_at', '>=', now()->subDays(30))->count();
        $previousPeriodTickets = SupportTicket::whereBetween('created_at', [Carbon::now()->subDays(60), Carbon::now()->subDays(30)])->count();

        // Generate chart data for the last 30 days
        $ticketCountChart = $this->getTicketCountChart();

        // Calculate the trend for tickets
        $ticketTrend = $this->calculateTrend($currentPeriodTickets, $previousPeriodTickets);
        $ticketTrendColor = $this->getTrendColor($ticketTrend);
        $ticketTrendIcon = $this->getTrendIcon($ticketTrend);
        $ticketTrendDescription = $this->getTrendDescription($ticketTrend);

        // Create the Support Ticket Count stat card
        $ticketCountCard = Stat::make(__('filament/resources/support-ticket-resource.tickets'), $currentPeriodTickets)
            ->description($ticketTrendDescription)
            ->descriptionIcon($ticketTrendIcon)
            ->chart($ticketCountChart)
            ->color($ticketTrendColor);

        return [
            $ticketCountCard,
        ];
    }

    // Helper function to calculate percentage trend
    protected function calculateTrend($current, $previous)
    {
        if ($previous == 0) {
            return 100; // If no previous data, treat as 100% increase
        }

        return (($current - $previous) / $previous) * 100;
    }

    // Helper function to determine color based on trend
    protected function getTrendColor($trend)
    {
        if ($trend > 0) {
            return 'success'; // Positive trend
        } elseif ($trend < 0) {
            return 'danger'; // Negative trend
        }
        return 'primary'; // No change
    }

    // Helper function to determine icon based on trend
    protected function getTrendIcon($trend)
    {
        if ($trend > 0) {
            return 'heroicon-m-arrow-trending-up';
        } elseif ($trend < 0) {
            return 'heroicon-m-arrow-trending-down';
        }
        return 'heroicon-m-minus'; // No change
    }

    // Helper function to get description based on trend
    protected function getTrendDescription($trend)
    {
        if ($trend > 0) {
            return number_format($trend, 2) . '% ' . __('filament/resources/support-ticket-resource.increase');
        } elseif ($trend < 0) {
            return number_format(abs($trend), 2) . '% ' . __('filament/resources/support-ticket-resource.decrease');
        }
        return 'No change';
    }

    // Fetch daily ticket counts for the last 30 days
    protected function getTicketCountChart()
    {
        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        return SupportTicket::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count')
            ->toArray();
    }
}
