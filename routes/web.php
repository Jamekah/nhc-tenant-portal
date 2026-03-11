<?php

use App\Livewire\Portal\CreateTicket as PortalCreateTicket;
use App\Livewire\Portal\Dashboard as PortalDashboard;
use App\Livewire\Portal\Invoices as PortalInvoices;
use App\Livewire\Portal\MakePayment as PortalMakePayment;
use App\Livewire\Portal\Payments as PortalPayments;
use App\Livewire\Portal\Profile as PortalProfile;
use App\Livewire\Portal\SupportTickets as PortalSupportTickets;
use App\Livewire\Portal\TicketDetail as PortalTicketDetail;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect('/admin/login');
});

// Portal logout
Route::post('/portal/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/admin/login');
})->name('portal.logout')->middleware('auth');

// Client Portal routes
Route::middleware(['auth', 'role:client'])->prefix('portal')->group(function () {
    Route::get('/', PortalDashboard::class)->name('portal.dashboard');
    Route::get('/invoices', PortalInvoices::class)->name('portal.invoices');
    Route::get('/payments', PortalPayments::class)->name('portal.payments');
    Route::get('/make-payment/{invoice?}', PortalMakePayment::class)->name('portal.make-payment');
    Route::get('/tickets', PortalSupportTickets::class)->name('portal.tickets');
    Route::get('/tickets/create', PortalCreateTicket::class)->name('portal.tickets.create');
    Route::get('/tickets/{ticket}', PortalTicketDetail::class)->name('portal.tickets.show');
    Route::get('/profile', PortalProfile::class)->name('portal.profile');

    // PDF downloads (portal)
    Route::get('/pdf/invoice/{invoice}', [PdfController::class, 'portalInvoice'])->name('portal.pdf.invoice');
    Route::get('/pdf/receipt/{payment}', [PdfController::class, 'portalReceipt'])->name('portal.pdf.receipt');
    Route::get('/pdf/statement', [PdfController::class, 'portalStatement'])->name('portal.pdf.statement');
});

// Admin PDF downloads
Route::middleware(['auth', 'role:super_admin|admin'])->prefix('admin/pdf')->group(function () {
    Route::get('/invoice/{invoice}', [PdfController::class, 'adminInvoice'])->name('admin.pdf.invoice');
    Route::get('/receipt/{payment}', [PdfController::class, 'adminReceipt'])->name('admin.pdf.receipt');
    Route::get('/arrears-report', [PdfController::class, 'arrearsReport'])->name('admin.pdf.arrears');
    Route::get('/revenue-report', [PdfController::class, 'revenueReport'])->name('admin.pdf.revenue');
});
