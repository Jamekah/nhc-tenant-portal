<?php

namespace App\Filament\Resources\SupportTicketResource\Pages;

use App\Filament\Resources\SupportTicketResource;
use App\Models\TicketComment;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewSupportTicket extends ViewRecord
{
    protected static string $resource = SupportTicketResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Ticket Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('ticket_number')
                            ->label('Ticket #')
                            ->weight('bold')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('category')
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

                        Infolists\Components\TextEntry::make('priority')
                            ->badge()
                            ->color(fn (string $state) => match ($state) {
                                'low' => 'gray',
                                'medium' => 'info',
                                'high' => 'warning',
                                'urgent' => 'danger',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state) => str($state)->replace('_', ' ')->title()->toString())
                            ->color(fn (string $state) => match ($state) {
                                'open' => 'warning',
                                'in_progress' => 'info',
                                'resolved' => 'success',
                                'closed' => 'gray',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('tenancy.tenant.name')
                            ->label('Submitted By'),

                        Infolists\Components\TextEntry::make('tenancy.property.property_code')
                            ->label('Property'),

                        Infolists\Components\TextEntry::make('assignee.name')
                            ->label('Assigned To')
                            ->placeholder('Unassigned'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Submitted At')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Issue Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('subject')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('description')
                            ->columnSpanFull()
                            ->prose(),
                    ]),

                Infolists\Components\Section::make('Conversation Thread')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('comments')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('')
                                    ->weight('bold')
                                    ->suffix(fn ($record) => $record->is_internal ? ' (Internal Note)' : '')
                                    ->color(fn ($record) => $record->is_internal ? 'danger' : 'primary'),

                                Infolists\Components\TextEntry::make('body')
                                    ->label('')
                                    ->prose(),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('')
                                    ->dateTime('d M Y H:i')
                                    ->color('gray'),
                            ])
                            ->columns(1),
                    ]),

                Infolists\Components\Section::make('Resolution')
                    ->schema([
                        Infolists\Components\TextEntry::make('resolved_at')
                            ->label('Resolved At')
                            ->dateTime('d M Y H:i')
                            ->placeholder('Not resolved yet'),
                    ])
                    ->visible(fn ($record) => $record->resolved_at !== null),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('addResponse')
                ->label('Add Response')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('primary')
                ->form([
                    Forms\Components\Textarea::make('body')
                        ->label('Response')
                        ->required()
                        ->rows(4),
                    Forms\Components\Toggle::make('is_internal')
                        ->label('Internal note (hidden from tenant)')
                        ->default(false),
                ])
                ->action(function (array $data) {
                    TicketComment::create([
                        'ticket_id' => $this->record->id,
                        'user_id' => auth()->id(),
                        'body' => $data['body'],
                        'is_internal' => $data['is_internal'],
                    ]);

                    Notification::make()
                        ->title($data['is_internal'] ? 'Internal note added' : 'Response sent')
                        ->success()
                        ->send();

                    $this->refreshFormData(['comments']);
                }),

            Actions\Action::make('assign')
                ->label('Assign')
                ->icon('heroicon-o-user-plus')
                ->color('info')
                ->form([
                    Forms\Components\Select::make('assigned_to')
                        ->label('Assign to Admin')
                        ->options(function () {
                            return \App\Models\User::role(['super_admin', 'admin'])
                                ->where('is_active', true)
                                ->pluck('name', 'id');
                        })
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'assigned_to' => $data['assigned_to'],
                        'status' => $this->record->status === 'open' ? 'in_progress' : $this->record->status,
                    ]);

                    Notification::make()
                        ->title('Ticket assigned successfully')
                        ->success()
                        ->send();
                })
                ->visible(fn () => in_array($this->record->status, ['open', 'in_progress'])),

            Actions\Action::make('resolve')
                ->label('Resolve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status' => 'resolved',
                        'resolved_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Ticket resolved')
                        ->success()
                        ->send();
                })
                ->visible(fn () => in_array($this->record->status, ['open', 'in_progress'])),

            Actions\EditAction::make(),
        ];
    }
}
