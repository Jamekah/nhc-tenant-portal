<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Arrears Report</title>
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
        .report-label h2 { font-size: 20px; color: #dc2626; font-weight: bold; }
        .report-label p { font-size: 10px; color: #666; margin-top: 2px; }

        .grand-total-box { background: #dc2626; color: white; border-radius: 6px; padding: 15px; text-align: center; margin-bottom: 20px; }
        .grand-total-box .label { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9; }
        .grand-total-box .amount { font-size: 28px; font-weight: bold; margin-top: 4px; }

        .region-header { background: #1e3a5f; color: white; padding: 8px 12px; border-radius: 4px 4px 0 0; margin-top: 15px; }
        .region-header h3 { font-size: 12px; display: inline; }
        .region-header .total { float: right; font-size: 12px; font-weight: bold; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9; color: #475569; padding: 6px 10px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; font-size: 10px; }
        tr:nth-child(even) { background: #f7f9fc; }

        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 8px; font-weight: bold; }
        .status-in_arrears { background: #fee2e2; color: #991b1b; }
        .status-overdue { background: #fef3c7; color: #92400e; }

        .region-subtotal { background: #f1f5f9; font-weight: bold; }

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
                    <h2>ARREARS REPORT</h2>
                    <p>Generated: {{ $generatedAt->format('d M Y, H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Grand Total -->
        <div class="grand-total-box">
            <div class="label">Total Outstanding Arrears</div>
            <div class="amount">PGK {{ number_format($grandTotal, 2) }}</div>
        </div>

        @if(count($reportData) > 0)
            @foreach($reportData as $regionData)
                <!-- Region Section -->
                <div class="region-header">
                    <h3>{{ $regionData['region_name'] }} ({{ $regionData['region_code'] }})</h3>
                    <span class="total">PGK {{ number_format($regionData['region_total'], 2) }}</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 25%;">Tenant Name</th>
                            <th style="width: 15%;">Property Code</th>
                            <th style="width: 25%;">Property</th>
                            <th style="width: 12%;">Status</th>
                            <th style="width: 18%; text-align: right;">Outstanding (PGK)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($regionData['tenants'] as $index => $tenantRow)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $tenantRow['tenant_name'] }}</strong></td>
                            <td>{{ $tenantRow['property_code'] }}</td>
                            <td>{{ $tenantRow['property_title'] ?? '-' }}</td>
                            <td>
                                <span class="status-badge status-{{ $tenantRow['tenant_status'] }}">
                                    {{ str($tenantRow['tenant_status'])->replace('_', ' ')->title() }}
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: bold; color: #dc2626;">{{ number_format($tenantRow['outstanding'], 2) }}</td>
                        </tr>
                        @endforeach
                        <tr class="region-subtotal">
                            <td colspan="5">Region Subtotal ({{ count($regionData['tenants']) }} tenants)</td>
                            <td style="text-align: right; color: #dc2626;">PGK {{ number_format($regionData['region_total'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endforeach
        @else
            <div class="no-data">
                <p>No tenants currently in arrears. All accounts are in good standing.</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ $orgName }}</strong> &mdash; Arrears Report &mdash; Confidential</p>
            <p>Generated on {{ $generatedAt->format('d M Y, H:i') }} | This report is for internal NHC use only.</p>
        </div>
    </div>
</body>
</html>
