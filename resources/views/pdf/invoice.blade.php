<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.5; }
        .page { padding: 30px 40px; }

        /* Header / Letterhead */
        .header { border-bottom: 3px solid #1e3a5f; padding-bottom: 15px; margin-bottom: 20px; }
        .header-top { display: table; width: 100%; }
        .logo-area { display: table-cell; vertical-align: middle; width: 60%; }
        .logo-area h1 { font-size: 20px; color: #1e3a5f; margin-bottom: 2px; }
        .logo-area p { font-size: 10px; color: #666; }
        .invoice-label { display: table-cell; vertical-align: middle; text-align: right; width: 40%; }
        .invoice-label h2 { font-size: 28px; color: #c8a415; font-weight: bold; letter-spacing: 2px; }

        /* Info Grid */
        .info-grid { display: table; width: 100%; margin-bottom: 20px; }
        .info-col { display: table-cell; vertical-align: top; width: 50%; }
        .info-col.right { text-align: right; }
        .info-box { background: #f7f9fc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px; margin-bottom: 10px; }
        .info-box h4 { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #1e3a5f; margin-bottom: 6px; font-weight: bold; }
        .info-box p { font-size: 11px; margin-bottom: 2px; }

        /* Invoice Details */
        .details-row { display: table; width: 100%; margin-bottom: 15px; }
        .detail-item { display: table-cell; text-align: center; padding: 8px; background: #1e3a5f; color: white; }
        .detail-item:first-child { border-radius: 4px 0 0 4px; }
        .detail-item:last-child { border-radius: 0 4px 4px 0; }
        .detail-item .label { font-size: 8px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8; }
        .detail-item .value { font-size: 13px; font-weight: bold; margin-top: 2px; }

        /* Table */
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #1e3a5f; color: white; padding: 8px 12px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        table.items td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; }
        table.items tr:nth-child(even) { background: #f7f9fc; }

        /* Totals */
        .totals { width: 280px; float: right; margin-bottom: 20px; }
        .totals-row { display: table; width: 100%; border-bottom: 1px solid #e2e8f0; }
        .totals-row .label { display: table-cell; padding: 6px 0; font-size: 11px; }
        .totals-row .amount { display: table-cell; padding: 6px 0; text-align: right; font-size: 11px; }
        .totals-row.total { border-bottom: 2px solid #1e3a5f; border-top: 2px solid #1e3a5f; }
        .totals-row.total .label, .totals-row.total .amount { font-weight: bold; font-size: 14px; color: #1e3a5f; padding: 8px 0; }
        .clear { clear: both; }

        /* Status Badge */
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .status-paid { background: #dcfce7; color: #166534; }
        .status-overdue { background: #fee2e2; color: #991b1b; }
        .status-sent { background: #fef3c7; color: #92400e; }
        .status-partial { background: #dbeafe; color: #1e40af; }
        .status-draft { background: #f3f4f6; color: #4b5563; }

        /* Payment History */
        table.payments { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.payments th { background: #f1f5f9; color: #475569; padding: 6px 10px; text-align: left; font-size: 9px; text-transform: uppercase; }
        table.payments td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }

        /* Payment Instructions */
        .payment-info { background: #fffbeb; border: 1px solid #fde68a; border-radius: 4px; padding: 12px; margin-bottom: 20px; }
        .payment-info h4 { color: #92400e; font-size: 11px; margin-bottom: 6px; }
        .payment-info p { font-size: 10px; color: #78350f; }

        /* Footer */
        .footer { border-top: 2px solid #1e3a5f; padding-top: 10px; margin-top: 20px; text-align: center; font-size: 9px; color: #666; }
        .footer strong { color: #1e3a5f; }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header / Letterhead -->
        <div class="header">
            <div class="header-top">
                <div class="logo-area">
                    <h1>{{ $orgName }}</h1>
                    <p>Papua New Guinea &mdash; Providing Quality Housing Solutions</p>
                </div>
                <div class="invoice-label">
                    <h2>INVOICE</h2>
                </div>
            </div>
        </div>

        <!-- Tenant & Invoice Info -->
        <div class="info-grid">
            <div class="info-col" style="padding-right: 15px;">
                <div class="info-box">
                    <h4>Bill To</h4>
                    <p><strong>{{ $tenant->name }}</strong></p>
                    <p>{{ $tenant->email }}</p>
                    @if($tenant->phone)<p>{{ $tenant->phone }}</p>@endif
                    <p style="margin-top: 4px;">{{ $property->address }}</p>
                    @if($property->suburb)<p>{{ $property->suburb }}, {{ $property->city }}</p>@endif
                    <p>{{ $property->province }}, Papua New Guinea</p>
                </div>
            </div>
            <div class="info-col right" style="padding-left: 15px;">
                <div class="info-box">
                    <h4>Invoice Details</h4>
                    <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
                    <p><strong>Date Issued:</strong> {{ $invoice->issued_at ? $invoice->issued_at->format('d M Y') : 'Not issued' }}</p>
                    <p><strong>Due Date:</strong> {{ $invoice->due_date->format('d M Y') }}</p>
                    <p><strong>Property:</strong> {{ $property->property_code }}</p>
                    <p><strong>Region:</strong> {{ $region->name }} ({{ $region->code }})</p>
                    <p><strong>Status:</strong>
                        <span class="status-badge status-{{ $invoice->status === 'partially_paid' ? 'partial' : $invoice->status }}">
                            {{ str($invoice->status)->replace('_', ' ')->title() }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Billing Period & Key Figures -->
        <div class="details-row">
            <div class="detail-item">
                <div class="label">Billing Period</div>
                <div class="value">{{ $invoice->billing_period_start->format('d M') }} &mdash; {{ $invoice->billing_period_end->format('d M Y') }}</div>
            </div>
            <div class="detail-item" style="background: #c8a415;">
                <div class="label">Amount Due</div>
                <div class="value">PGK {{ number_format($invoice->amount_due, 2) }}</div>
            </div>
            <div class="detail-item">
                <div class="label">Balance Outstanding</div>
                <div class="value">PGK {{ number_format($invoice->balance, 2) }}</div>
            </div>
        </div>

        <!-- Line Items -->
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th>Period</th>
                    <th>Frequency</th>
                    <th style="text-align: right;">Amount (PGK)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Monthly Rent</strong> &mdash; {{ $property->title ?? $property->property_code }}</td>
                    <td>{{ $invoice->billing_period_start->format('M Y') }}</td>
                    <td>{{ str($tenancy->payment_frequency)->title() }}</td>
                    <td style="text-align: right;">{{ number_format($invoice->amount_due, 2) }}</td>
                </tr>
                @if($invoice->notes)
                <tr>
                    <td colspan="4" style="font-style: italic; color: #666;">Note: {{ $invoice->notes }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <div class="totals-row">
                <span class="label">Subtotal</span>
                <span class="amount">PGK {{ number_format($invoice->amount_due, 2) }}</span>
            </div>
            <div class="totals-row">
                <span class="label">Amount Paid</span>
                <span class="amount" style="color: #166534;">- PGK {{ number_format($invoice->amount_paid, 2) }}</span>
            </div>
            <div class="totals-row total">
                <span class="label">Balance Due</span>
                <span class="amount">PGK {{ number_format($invoice->balance, 2) }}</span>
            </div>
        </div>
        <div class="clear"></div>

        <!-- Payment History -->
        @if($payments->count() > 0)
        <h4 style="font-size: 12px; color: #1e3a5f; margin-bottom: 8px;">Payment History</h4>
        <table class="payments">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th style="text-align: right;">Amount (PGK)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->paid_at->format('d M Y') }}</td>
                    <td>{{ str($payment->payment_method)->replace('_', ' ')->title() }}</td>
                    <td>{{ $payment->gateway_transaction_id ?? $payment->reference_number ?? '-' }}</td>
                    <td style="text-align: right;">{{ number_format($payment->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Payment Instructions -->
        @if($invoice->balance > 0)
        <div class="payment-info">
            <h4>Payment Instructions</h4>
            <p>Payment can be made through the NHC Tenant Portal at <strong>portal.nhc.gov.pg</strong>, by bank transfer, or in person at any NHC regional office.</p>
            <p style="margin-top: 4px;">Please quote your invoice number <strong>{{ $invoice->invoice_number }}</strong> with all payments.</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ $orgName }}</strong> &mdash; Papua New Guinea</p>
            <p>Email: {{ $supportEmail }} | Phone: {{ $supportPhone }}</p>
            <p style="margin-top: 4px;">This is a computer-generated document. No signature is required.</p>
        </div>
    </div>
</body>
</html>
