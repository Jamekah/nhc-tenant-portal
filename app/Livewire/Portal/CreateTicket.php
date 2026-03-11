<?php

namespace App\Livewire\Portal;

use App\Models\SupportTicket;
use App\Models\Tenancy;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateTicket extends Component
{
    public string $category = '';
    public string $subject = '';
    public string $description = '';
    public string $priority = 'medium';

    public bool $submitted = false;
    public ?string $ticketNumber = null;

    protected function rules(): array
    {
        return [
            'category' => 'required|in:maintenance,plumbing,electrical,structural,general_query,billing',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'priority' => 'required|in:low,medium,high,urgent',
        ];
    }

    protected function messages(): array
    {
        return [
            'description.min' => 'Please provide at least 10 characters describing the issue.',
        ];
    }

    public function submit()
    {
        $this->validate();

        $user = Auth::user();

        $tenancy = Tenancy::where('tenant_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$tenancy) {
            session()->flash('error', 'No active tenancy found.');
            return;
        }

        // Generate ticket number: TKT-YYYY-NNNNNN
        $year = now()->format('Y');
        $lastTicket = SupportTicket::where('ticket_number', 'like', "TKT-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket->ticket_number, -6);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        $ticketNumber = "TKT-{$year}-" . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        $ticket = SupportTicket::create([
            'tenancy_id' => $tenancy->id,
            'submitted_by' => $user->id,
            'ticket_number' => $ticketNumber,
            'category' => $this->category,
            'subject' => $this->subject,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => 'open',
        ]);

        $this->submitted = true;
        $this->ticketNumber = $ticket->ticket_number;
        $this->dispatch('toast', type: 'success', message: 'Support ticket submitted successfully!');
    }

    public function render()
    {
        $user = Auth::user();
        $tenancy = Tenancy::where('tenant_id', $user->id)
            ->where('status', 'active')
            ->with('property')
            ->first();

        return view('livewire.portal.create-ticket', [
            'tenancy' => $tenancy,
        ])->layout('layouts.portal');
    }
}
