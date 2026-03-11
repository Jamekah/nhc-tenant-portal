<?php

namespace App\Livewire\Portal;

use App\Models\Payment;
use App\Models\Tenancy;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Payments extends Component
{
    use WithPagination;

    public function render()
    {
        $user = Auth::user();
        $tenancyIds = Tenancy::where('tenant_id', $user->id)->pluck('id');

        $payments = Payment::whereIn('tenancy_id', $tenancyIds)
            ->with(['invoice', 'tenancy.property'])
            ->orderBy('paid_at', 'desc')
            ->paginate(10);

        return view('livewire.portal.payments', [
            'payments' => $payments,
        ])->layout('layouts.portal');
    }
}
