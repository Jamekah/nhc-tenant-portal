<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Statement - {{ $periodStart->format('F Y') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.5; }
        .page { padding: 30px 40px; }

        .header { border-bottom: 3px solid #1e3a5f; padding-bottom: 15px; margin-bottom: 20px; }
        .header-top { display: table; width: 100%; }
        .logo-area { display: table-cell; vertical-align: middle; width: 60%; }
        .logo-area h1 { font-size: 20px; color: #1e3a5f; margin-bottom: 2px; }
        .logo-area p { font-size: 10px; color: #666; }
        .stmt-label { display: table-cell; vertical-align: middle; text-align: right; width: 40%; }
        .stmt-label h2 { font-size: 22px; color: #1e3a5f; font-weight: bold; letter-spacing: 1px; }
        .stmt-label p { font-size: 14px; color: #c8a415; font-weight: bold; margin-top: 2px; }

        .info-grid { display: table; width: 100%; margin-bottom: 20px; }
        .info-col { display: table-cell; vertical-align: top; width: 50%; }
        .info-box { background: #f7f9fc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px; }
        .info-box h4 { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #1e3a5f; margin-bottom: 6px; font-weight: bold; }
        .info-box p { font-size: 11px; margin-bottom: 2px; }

        .summary-grid { display: table; width: 100%; margin-bottom: 20px; }
        .summary-item { display: table-cell; text-align: center; padding: 12px; border: 1px solid #e2e8f0; }
        .summary-item .label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #666; }
        .summary-item .value { font-size: 16px; font-weight: bold; margin-top: 4px; }
        .summary-item.highlight { background: #1e3a5f; color: white; border-color: #1e3a5f; }
        .summary-item.highlight .label { color: rgba(255,255,255,0.8); }
        .summary-item.danger .value { color: #dc2626; }
        .summary-item.success .value { color: #166534; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #1e3a5f; color: white; padding: 7px 10px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
        tr:nth-child(even) { background: #f7f9fc; }

        .section-title { font-size: 12px; color: #1e3a5f; margin: 15px 0 8px 0; font-weight: bold; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }

        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .status-paid { background: #dcfce7; color: #166534; }
        .status-overdue { background: #fee2e2; color: #991b1b; }
        .status-sent { background: #fef3c7; color: #92400e; }
        .status-partial { background: #dbeafe; color: #1e40af; }

        .footer { border-top: 2px solid #1e3a5f; padding-top: 10px; margin-top: 25px; text-align: center; font-size: 9px; color: #666; }
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
                <div class="stmt-label">
                    <h2>STATEMENT</h2>
                    <p>{{ $periodStart->format('F Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Tenant & Property Info -->
        <div class="info-grid">
            <div class="info-col" style="padding-right: 15px;">
                <div class="info-box">
                    <h4>Tenant</h4>
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
                    <p>{{ $region->name }} Region | Rent: PGK {{ number_format($tenancy->agreed_rent, 2) }}/{{ $tenancy->payment_frequency }}</p>
                </div>
            </div>
        </div>

        <!-- Summary Boxes -->
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Opening Balance</div>
                <div class="value">PGK {{ number_format($openingBalance, 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Charges This Month</div>
                <div class="value">PGK {{ number_format($totalCharges, 2) }}</div>
            </div>
            <div class="summary-item success">
                <div class="label">Payments Received</div>
                <div class="value">PGK {{ number_format($totalPayments, 2) }}</div>
            </div>
            <div class="summary-item highlight">
                <div class="label">Closing Balance</div>
                <div class="value">PGK {{ number_format($closingBalance, 2) }}</div>
            </div>
        </div>

        <!-- Invoices -->
        <div class="section-title">Invoices &mdash; {{ $periodStart->format('F Y') }}</div>
        @if($invoices->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Period</th>
                    <th>Due Date</th>
                    <th style="text-align: right;">Amount (PGK)</th>
                    <th style="text-align: right;">Paid (PGK)</th>
                    <th style="text-align: right;">Balance (PGK)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $inv)
                <tr>
                    <td>{{ $inv->invoice_number }}</td>
                    <td>{{ $inv->billing_period_start->format('d M') }} - {{ $inv->billing_period_end->format('d M') }}</td>
                    <td>{{ $inv->due_date->format('d M Y') }}</td>
                    <td style="text-align: right;">{{ number_format($inv->amount_due, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($inv->amount_paid, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($inv->balance, 2) }}</td>
                    <td>
                        <span class="status-badge status-{{ $inv->status === 'partially_paid' ? 'partial' : $inv->status }}">
                            {{ str($inv->status)->replace('_', ' ')->title() }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="color: #666; padding: 10px 0;">No invoices for this period.</p>
        @endif

        <!-- Payments -->
        <div class="section-title">Payments &mdash; {{ $periodStart->format('F Y') }}</div>
        @if($payments->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th style="text-align: right;">Amount (PGK)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $pmt)
                <tr>
                    <td>{{ $pmt->paid_at->format('d M Y, H:i') }}</td>
                    <td>{{ str($pmt->payment_method)->replace('_', ' ')->title() }}</td>
                    <td>{{ $pmt->gateway_transaction_id ?? $pmt->reference_number ?? '-' }}</td>
                    <td style="text-align: right;">{{ number_format($pmt->amount, 2) }}</td>
                    <td style="color: #166534; font-weight: bold;">{{ str($pmt->status)->title() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="color: #666; padding: 10px 0;">No payments recorded for this period.</p>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ $orgName }}</strong> &mdash; Papua New Guinea</p>
            <p>Email: {{ $supportEmail }} | Phone: {{ $supportPhone }}</p>
            <p style="margin-top: 4px;">Statement generated on {{ now()->format('d M Y, H:i') }}</p>
        </div>
    </div>
</body>
</html>
