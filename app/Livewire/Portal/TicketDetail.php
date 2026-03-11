<?php

namespace App\Livewire\Portal;

use App\Models\SupportTicket;
use App\Models\Tenancy;
use App\Models\TicketComment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TicketDetail extends Component
{
    public SupportTicket $ticket;

    public string $newComment = '';
    public bool $commentSent = false;

    public function mount(SupportTicket $ticket)
    {
        $user = Auth::user();

        // Verify this ticket belongs to the authenticated tenant
        $tenancy = Tenancy::where('tenant_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$tenancy || $ticket->tenancy_id !== $tenancy->id) {
            abort(403, 'Unauthorized access to this ticket.');
        }

        $this->ticket = $ticket;
    }

    public function addComment()
    {
        $this->validate([
            'newComment' => 'required|string|min:2',
        ]);

        TicketComment::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'body' => $this->newComment,
            'is_internal' => false,
        ]);

        // If ticket was resolved or closed, reopen it when tenant responds
        if (in_array($this->ticket->status, ['resolved', 'closed'])) {
            $this->ticket->update(['status' => 'open']);
        }

        $this->newComment = '';
        $this->commentSent = true;
        $this->ticket->refresh();
        $this->dispatch('toast', type: 'success', message: 'Reply sent successfully!');
    }

    #[Computed]
    public function comments()
    {
        return $this->ticket->comments()
            ->where('is_internal', false) // Never show internal notes to tenants
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function render()
    {
        return view('livewire.portal.ticket-detail', [
            'comments' => $this->comments,
        ])->layout('layouts.portal');
    }
}
