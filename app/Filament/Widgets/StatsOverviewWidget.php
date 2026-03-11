<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SupportTicket;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalTenants = User::role('client')->where('is_active', true)->count();

        $totalRevenue = Payment::where('status', 'completed')->sum('amount');

        $overdueCount = Invoice::where('status', 'overdue')->count();

        $activeTickets = SupportTicket::whereIn('status', ['open', 'in_progress'])->count();

        return [
            Stat::make('Total Active Tenants', $totalTenants)
                ->description('Currently active tenants')
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5]),

            Stat::make('Total Revenue (PGK)', 'K ' . number_format($totalRevenue, 2))
                ->description('All completed payments')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary')
                ->chart([3, 5, 7, 6, 8, 5, 9]),

            Stat::make('Overdue Invoices', $overdueCount)
                ->description('Invoices past due date')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($overdueCount > 0 ? 'danger' : 'success')
                ->chart([5, 3, 4, 2, 6, 3, 2]),

            Stat::make('Active Tickets', $activeTickets)
                ->description('Open support tickets')
                ->descriptionIcon('heroicon-m-ticket')
                ->color($activeTickets > 0 ? 'warning' : 'success')
                ->chart([2, 3, 1, 4, 2, 3, 1]),
        ];
    }
}
