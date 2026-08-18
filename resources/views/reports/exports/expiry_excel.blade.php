<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Medicine Expiry Audit</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 11pt; color: #1e293b; margin: 15px; }
        .report-header { margin-bottom: 20px; }
        .company-name { font-size: 16pt; font-weight: bold; color: #0284c7; margin-bottom: 4px; }
        .report-title { font-size: 13pt; font-weight: bold; color: #334155; margin-bottom: 6px; }
        .meta-info { font-size: 9.5pt; color: #64748b; }
        
        .kpi-table { margin-bottom: 20px; border-collapse: collapse; }
        .kpi-table td { padding: 8px 14px; border: 1px solid #cbd5e1; font-size: 10pt; }
        .kpi-label { background-color: #f1f5f9; font-weight: bold; color: #475569; }
        .kpi-val { background-color: #ffffff; font-weight: bold; color: #0f172a; text-align: right; }
        .kpi-alert { background-color: #ffe4e6; color: #e11d48; }
        
        .main-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .main-table th { background-color: #0284c7; color: #ffffff; font-weight: bold; font-size: 10.5pt; padding: 10px 12px; border: 1px solid #0369a1; text-align: left; }
        .main-table th.th-center { text-align: center; }
        .main-table th.th-right { text-align: right; }
        
        .main-table td { padding: 8px 12px; border: 1px solid #cbd5e1; font-size: 10pt; color: #334155; }
        .main-table tr.even-row { background-color: #f8fafc; }
        .main-table tr.odd-row { background-color: #ffffff; }
        
        .cell-text { text-align: left; mso-number-format: "\@"; }
        .cell-bold { text-align: left; font-weight: bold; color: #0f172a; mso-number-format: "\@"; }
        .cell-code { text-align: center; font-family: Consolas, monospace; font-weight: bold; mso-number-format: "\@"; }
        .cell-center { text-align: center; mso-number-format: "\@"; }
        .cell-num { text-align: right; mso-number-format: "#,##0.00"; }
        .cell-qty { text-align: center; font-weight: bold; mso-number-format: "#,##0"; }
        
        .footer-total-title { background-color: #f1f5f9; font-weight: bold; font-size: 11pt; text-align: right; padding: 10px 12px; border-top: 2px solid #0284c7; border-bottom: 3px double #0284c7; color: #1e293b; }
        .footer-total-value { background-color: #ffe4e6; font-weight: bold; font-size: 12pt; color: #e11d48; text-align: right; padding: 10px 12px; border-top: 2px solid #0284c7; border-bottom: 3px double #0284c7; mso-number-format: "#,##0.00"; }
    </style>
</head>
<body>

    <div class="report-header">
        <div class="company-name">{{ $pharmacy }}</div>
        <div class="report-title">Medicine Expiration & Risk Audit Report</div>
        <div class="meta-info">
            <strong>Filter Period:</strong> {{ $from }} to {{ $to }} &nbsp;|&nbsp; 
            <strong>Generated:</strong> {{ date('d M Y, h:i A') }} &nbsp;|&nbsp; 
            <strong>Currency:</strong> {{ $currency }}
        </div>
    </div>

    <table class="kpi-table">
        <tr>
            <td class="kpi-label">Expiring Batches</td>
            <td class="kpi-val" style="mso-number-format:'0';">{{ $totalBatches }}</td>
            <td class="kpi-label">Total Units at Risk</td>
            <td class="kpi-val" style="mso-number-format:'0';">{{ number_format($totalQuantity) }}</td>
            <td class="kpi-label kpi-alert">Total Financial Risk Exposure ({{ $currency }})</td>
            <td class="kpi-val kpi-alert">{{ number_format($totalValue, 2) }}</td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 180px;">Medicine Name</th>
                <th class="th-center" style="width: 110px;">Batch #</th>
                <th style="width: 150px;">Supplier / Distributor</th>
                <th class="th-center" style="width: 110px;">Expiry Date</th>
                <th class="th-center" style="width: 120px;">Days Remaining</th>
                <th class="th-center" style="width: 90px;">Stock Qty</th>
                <th class="th-right" style="width: 120px;">Unit Cost ({{ $currency }})</th>
                <th class="th-right" style="width: 140px;">Total Loss Risk ({{ $currency }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($batches as $idx => $batch)
                @php
                    $rowClass = ($idx % 2 === 0) ? 'even-row' : 'odd-row';
                    $days = \Carbon\Carbon::now()->diffInDays($batch->expiry_date, false);
                    $daysText = $days < 0 ? 'EXPIRED (' . abs((int)$days) . 'd ago)' : ((int)$days . ' days');
                    $daysColor = $days < 0 ? '#e11d48' : ($days <= 30 ? '#ea580c' : '#d97706');
                    $riskVal = (float)($batch->purchase_price * $batch->quantity);
                @endphp
                <tr class="{{ $rowClass }}">
                    <td class="cell-bold">{{ $batch->medicine?->name ?? 'Unknown Medicine' }}</td>
                    <td class="cell-code">{{ $batch->batch_number }}</td>
                    <td class="cell-text">{{ $batch->supplier?->name ?? 'Direct Purchase' }}</td>
                    <td class="cell-center">{{ $batch->expiry_date->format('d-M-Y') }}</td>
                    <td class="cell-center" style="font-weight: bold; color: {{ $daysColor }};">{{ $daysText }}</td>
                    <td class="cell-qty">{{ $batch->quantity }}</td>
                    <td class="cell-num">{{ number_format($batch->purchase_price, 2) }}</td>
                    <td class="cell-num" style="font-weight: bold; color: #e11d48;">{{ number_format($riskVal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="cell-center" style="padding: 24px; color: #94a3b8;">No batches expiring in the selected time window.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="footer-total-title">TOTAL UNITS & RISK EXPOSURE:</td>
                <td class="footer-total-value" style="text-align: center; mso-number-format:'#,##0'; color: #0369a1; background-color: #e0f2fe;">{{ number_format($totalQuantity) }}</td>
                <td class="footer-total-title">TOTAL ({{ $currency }}):</td>
                <td class="footer-total-value">{{ number_format($totalValue, 2) }}</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
