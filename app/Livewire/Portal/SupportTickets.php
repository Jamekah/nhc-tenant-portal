<?php

namespace App\Livewire\Portal;

use App\Models\SupportTicket;
use App\Models\Tenancy;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SupportTickets extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        $tenancy = Tenancy::where('tenant_id', $user->id)
            ->where('status', 'active')
            ->first();

        $tickets = collect();
        $ticketCounts = [
            'all' => 0,
            'open' => 0,
            'in_progress' => 0,
            'resolved' => 0,
            'closed' => 0,
        ];

        if ($tenancy) {
            $query = SupportTicket::where('tenancy_id', $tenancy->id);

            // Get counts for status tabs
            $ticketCounts['all'] = (clone $query)->count();
            $ticketCounts['open'] = (clone $query)->where('status', 'open')->count();
            $ticketCounts['in_progress'] = (clone $query)->where('status', 'in_progress')->count();
            $ticketCounts['resolved'] = (clone $query)->where('status', 'resolved')->count();
            $ticketCounts['closed'] = (clone $query)->where('status', 'closed')->count();

            if ($this->statusFilter) {
                $query->where('status', $this->statusFilter);
            }

            $tickets = $query->orderBy('created_at', 'desc')->paginate(10);
        }

        return view('livewire.portal.support-tickets', [
            'tickets' => $tickets,
            'ticketCounts' => $ticketCounts,
            'tenancy' => $tenancy,
        ])->layout('layouts.portal');
    }
}
