<?php

namespace App\Filament\Widgets;

use App\Models\SupportTicket;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TicketOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Support Tickets';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SupportTicket::query()
                    ->whereIn('status', ['open', 'in_progress'])
                    ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('Ticket #')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('tenancy.tenant.name')
                    ->label('Tenant'),

                Tables\Columns\TextColumn::make('subject')
                    ->limit(35),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->replace('_', ' ')->title()->toString())
                    ->color(fn (string $state) => match ($state) {
                        'maintenance' => 'primary',
                        'plumbing' => 'info',
                        'electrical' => 'warning',
                        'structural' => 'danger',
                        'billing' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'low' => 'gray',
                        'medium' => 'info',
                        'high' => 'warning',
                        'urgent' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->replace('_', ' ')->title()->toString())
                    ->color(fn (string $state) => match ($state) {
                        'open' => 'warning',
                        'in_progress' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Assigned To')
                    ->placeholder('Unassigned'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (SupportTicket $record) => route('filament.admin.resources.support-tickets.view', $record)),
            ])
            ->paginated(false);
    }
}
