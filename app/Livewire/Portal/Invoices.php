<?php

namespace App\Livewire\Portal;

use App\Models\Invoice;
use App\Models\Tenancy;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Invoices extends Component
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
        $tenancyIds = Tenancy::where('tenant_id', $user->id)->pluck('id');

        $query = Invoice::whereIn('tenancy_id', $tenancyIds)
            ->with('tenancy.property')
            ->orderBy('created_at', 'desc');

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.portal.invoices', [
            'invoices' => $query->paginate(10),
        ])->layout('layouts.portal');
    }
}
