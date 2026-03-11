<?php

namespace App\Filament\Resources\SupportTicketResource\Pages;

use App\Filament\Resources\SupportTicketResource;
use App\Models\TicketComment;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSupportTicket extends EditRecord
{
    protected static string $resource = SupportTicketResource::class;

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
                }),

            Actions\Action::make('resolve')
                ->label('Resolve Ticket')
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
                    $this->refreshFormData(['status', 'resolved_at']);
                })
                ->visible(fn () => in_array($this->record->status, ['open', 'in_progress'])),

            Actions\Action::make('close')
                ->label('Close Ticket')
                ->icon('heroicon-o-x-circle')
                ->color('gray')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status' => 'closed',
                        'resolved_at' => $this->record->resolved_at ?? now(),
                    ]);
                    Notification::make()
                        ->title('Ticket closed')
                        ->success()
                        ->send();
                    $this->refreshFormData(['status', 'resolved_at']);
                })
                ->visible(fn () => $this->record->status !== 'closed'),

            Actions\ViewAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Auto-set resolved_at when status changes to resolved/closed
        if (in_array($data['status'] ?? '', ['resolved', 'closed']) && !$this->record->resolved_at) {
            $data['resolved_at'] = now();
        }

        return $data;
    }
}
