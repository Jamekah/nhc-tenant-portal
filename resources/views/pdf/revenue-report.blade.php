<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Revenue Collection Report - {{ $periodStart->format('F Y') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .page { padding: 25px 30px; }

        .header { border-bottom: 3px solid #1e3a5f; padding-bottom: 12px; margin-bottom: 15px; }
        .header-top { display: table; width: 100%; }
        .logo-area { display: table-cell; vertical-align: middle; width: 50%; }
        .logo-area h1 { font-size: 18px; color: #1e3a5f; margin-bottom: 2px; }
        .logo-area p { font-size: 9px; color: #666; }
        .report-label { display: table-cell; vertical-align: middle; text-align: right; width: 50%; }
        .report-label h2 { font-size: 18px; color: #1e3a5f; font-weight: bold; }
        .report-label p { font-size: 11px; color: #c8a415; font-weight: bold; margin-top: 2px; }
        .report-label .date { font-size: 9px; color: #666; margin-top: 2px; }

        .summary-grid { display: table; width: 100%; margin-bottom: 20px; }
        .summary-item { display: table-cell; text-align: center; padding: 15px; border: 1px solid #e2e8f0; }
        .summary-item.highlight { background: #1e3a5f; color: white; border-color: #1e3a5f; border-radius: 6px 0 0 6px; }
        .summary-item.highlight .label { color: rgba(255,255,255,0.8); }
        .summary-item .label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #666; }
        .summary-item .value { font-size: 20px; font-weight: bold; margin-top: 4px; }

        .region-header { background: #1e3a5f; color: white; padding: 8px 12px; border-radius: 4px 4px 0 0; margin-top: 15px; }
        .region-header h3 { font-size: 12px; display: inline; }
        .region-header .total { float: right; font-size: 12px; font-weight: bold; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9; color: #475569; padding: 6px 10px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
        tr:nth-child(even) { background: #f7f9fc; }

        .region-subtotal { background: #f1f5f9; font-weight: bold; }

        .grand-total { background: #1e3a5f; color: white; font-weight: bold; font-size: 11px; }
        .grand-total td { padding: 10px; border: none; }

        .footer { border-top: 2px solid #1e3a5f; padding-top: 8px; margin-top: 20px; text-align: center; font-size: 8px; color: #666; }
        .footer strong { color: #1e3a5f; }

        .no-data { text-align: center; padding: 30px; color: #666; font-size: 14px; }
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
                <div class="report-label">
                    <h2>REVENUE COLLECTION</h2>
                    <p>{{ $periodStart->format('F Y') }}</p>
                    <p class="date">Generated: {{ $generatedAt->format('d M Y, H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="summary-grid">
            <div class="summary-item highlight">
                <div class="label">Total Revenue Collected</div>
                <div class="value">PGK {{ number_format($grandTotal, 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Transactions</div>
                <div class="value">{{ $grandCount }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Regions Active</div>
                <div class="value">{{ count($reportData) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Reporting Period</div>
                <div class="value" style="font-size: 14px;">{{ $periodStart->format('M Y') }}</div>
            </div>
        </div>

        @if(count($reportData) > 0)
            <!-- Combined Table -->
            <table>
                <thead>
                    <tr>
                        <th style="width: 25%;">Region</th>
                        <th style="width: 25%;">Payment Method</th>
                        <th style="width: 15%; text-align: center;">Transactions</th>
                        <th style="width: 20%; text-align: right;">Amount (PGK)</th>
                        <th style="width: 15%; text-align: right;">% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData as $regionData)
                        @foreach($regionData['methods'] as $index => $method)
                        <tr>
                            @if($index === 0)
                            <td rowspan="{{ count($regionData['methods']) }}" style="vertical-align: top; font-weight: bold;">
                                {{ $regionData['region_name'] }}<br>
                                <span style="font-size: 9px; color: #666; font-weight: normal;">({{ $regionData['region_code'] }})</span>
                            </td>
                            @endif
                            <td>{{ $method['method'] }}</td>
                            <td style="text-align: center;">{{ $method['count'] }}</td>
                            <td style="text-align: right;">{{ number_format($method['total'], 2) }}</td>
                            <td style="text-align: right;">{{ $grandTotal > 0 ? number_format(($method['total'] / $grandTotal) * 100, 1) : 0 }}%</td>
                        </tr>
                        @endforeach
                        <tr class="region-subtotal">
                            <td>{{ $regionData['region_name'] }} Subtotal</td>
                            <td></td>
                            <td style="text-align: center;">{{ $regionData['region_count'] }}</td>
                            <td style="text-align: right;">PGK {{ number_format($regionData['region_total'], 2) }}</td>
                            <td style="text-align: right;">{{ $grandTotal > 0 ? number_format(($regionData['region_total'] / $grandTotal) * 100, 1) : 0 }}%</td>
                        </tr>
                    @endforeach
                    <tr class="grand-total">
                        <td colspan="2">GRAND TOTAL</td>
                        <td style="text-align: center;">{{ $grandCount }}</td>
                        <td style="text-align: right;">PGK {{ number_format($grandTotal, 2) }}</td>
                        <td style="text-align: right;">100%</td>
                    </tr>
                </tbody>
            </table>
        @else
            <div class="no-data">
                <p>No payments recorded for {{ $periodStart->format('F Y') }}.</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ $orgName }}</strong> &mdash; Revenue Collection Report &mdash; Confidential</p>
            <p>Period: {{ $periodStart->format('d M Y') }} to {{ $periodEnd->format('d M Y') }} | Generated on {{ $generatedAt->format('d M Y, H:i') }}</p>
            <p>This report is for internal NHC use only.</p>
        </div>
    </div>
</body>
</html>
