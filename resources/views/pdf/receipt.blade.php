<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.5; }
        .page { padding: 30px 40px; }

        .header { border-bottom: 3px solid #1e3a5f; padding-bottom: 15px; margin-bottom: 25px; }
        .header-top { display: table; width: 100%; }
        .logo-area { display: table-cell; vertical-align: middle; width: 60%; }
        .logo-area h1 { font-size: 20px; color: #1e3a5f; margin-bottom: 2px; }
        .logo-area p { font-size: 10px; color: #666; }
        .receipt-label { display: table-cell; vertical-align: middle; text-align: right; width: 40%; }
        .receipt-label h2 { font-size: 24px; color: #166534; font-weight: bold; letter-spacing: 2px; }

        .success-banner { background: #dcfce7; border: 1px solid #86efac; border-radius: 6px; padding: 15px; text-align: center; margin-bottom: 25px; }
        .success-banner h3 { color: #166534; font-size: 16px; }
        .success-banner p { color: #15803d; font-size: 12px; margin-top: 4px; }

        .info-grid { display: table; width: 100%; margin-bottom: 20px; }
        .info-col { display: table-cell; vertical-align: top; width: 50%; }
        .info-box { background: #f7f9fc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px; }
        .info-box h4 { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #1e3a5f; margin-bottom: 6px; font-weight: bold; }
        .info-box p { font-size: 11px; margin-bottom: 2px; }

        .amount-box { background: #1e3a5f; color: white; border-radius: 6px; padding: 20px; text-align: center; margin-bottom: 25px; }
        .amount-box .label { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8; }
        .amount-box .amount { font-size: 32px; font-weight: bold; margin-top: 5px; }
        .amount-box .currency { font-size: 14px; opacity: 0.8; }

        table.details { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.details td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; }
        table.details td:first-child { font-weight: bold; color: #475569; width: 40%; }
        table.details tr:nth-child(even) { background: #f7f9fc; }

        .footer { border-top: 2px solid #1e3a5f; padding-top: 10px; margin-top: 30px; text-align: center; font-size: 9px; color: #666; }
        .footer strong { color: #1e3a5f; }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div class="logo-area">
                    <h1>{{ $orgName }}</h1>
                    <p>Papua New Guinea &mdash; Providing Quality Housing Solutions</p>
                </div>
                <div class="receipt-label">
                    <h2>RECEIPT</h2>
                </div>
            </div>
        </div>

        <!-- Success Banner -->
        <div class="success-banner">
            <h3>Payment Confirmed</h3>
            <p>Transaction ID: {{ $payment->gateway_transaction_id ?? $payment->reference_number ?? 'N/A' }}</p>
        </div>

        <!-- Amount Box -->
        <div class="amount-box">
            <div class="label">Amount Paid</div>
            <div class="amount"><span class="currency">PGK</span> {{ number_format($payment->amount, 2) }}</div>
        </div>

        <!-- Tenant & Property Info -->
        <div class="info-grid">
            <div class="info-col" style="padding-right: 15px;">
                <div class="info-box">
                    <h4>Received From</h4>
                    <p><strong>{{ $tenant->name }}</strong></p>
                    <p>{{ $tenant->email }}</p>
                    @if($tenant->phone)<p>{{ $tenant->phone }}</p>@endif
                </div>
            </div>
            <div class="info-col" style="padding-left: 15px;">
                <div class="info-box">
                    <h4>Property</h4>
                    <p><strong>{{ $property->property_code }}</strong> &mdash; {{ $property->title ?? '' }}</p>
                    <p>{{ $property->address }}</p>
                    <p>{{ $region->name }} Region</p>
                </div>
            </div>
        </div>

        <!-- Payment Details -->
        <h4 style="font-size: 12px; color: #1e3a5f; margin-bottom: 8px; margin-top: 15px;">Payment Details</h4>
        <table class="details">
            <tr>
                <td>Payment Date</td>
                <td>{{ $payment->paid_at ? $payment->paid_at->format('d M Y, H:i') : 'N/A' }}</td>
            </tr>
            <tr>
                <td>Payment Method</td>
                <td>{{ str($payment->payment_method)->replace('_', ' ')->title() }}</td>
            </tr>
            <tr>
                <td>Transaction ID</td>
                <td>{{ $payment->gateway_transaction_id ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Reference Number</td>
                <td>{{ $payment->reference_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Payment Status</td>
                <td style="color: #166534; font-weight: bold;">{{ str($payment->status)->title() }}</td>
            </tr>
            @if($payment->recorder)
            <tr>
                <td>Recorded By</td>
                <td>{{ $payment->recorder->name }}</td>
            </tr>
            @endif
            @if($payment->notes)
            <tr>
                <td>Notes</td>
                <td>{{ $payment->notes }}</td>
            </tr>
            @endif
        </table>

        <!-- Invoice Reference -->
        @if($invoice)
        <h4 style="font-size: 12px; color: #1e3a5f; margin-bottom: 8px;">Invoice Reference</h4>
        <table class="details">
            <tr>
                <td>Invoice Number</td>
                <td>{{ $invoice->invoice_number }}</td>
            </tr>
            <tr>
                <td>Billing Period</td>
                <td>{{ $invoice->billing_period_start->format('d M Y') }} &mdash; {{ $invoice->billing_period_end->format('d M Y') }}</td>
            </tr>
            <tr>
                <td>Invoice Amount</td>
                <td>PGK {{ number_format($invoice->amount_due, 2) }}</td>
            </tr>
            <tr>
                <td>Remaining Balance</td>
                <td>PGK {{ number_format($invoice->balance, 2) }}</td>
            </tr>
        </table>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ $orgName }}</strong> &mdash; Papua New Guinea</p>
            <p>Email: {{ $supportEmail }} | Phone: {{ $supportPhone }}</p>
            <p style="margin-top: 4px;">This is a computer-generated receipt. No signature is required.</p>
            <p>Generated on {{ now()->format('d M Y, H:i') }}</p>
        </div>
    </div>
</body>
</html>
