<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
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
        .kpi-highlight { background-color: #e0f2fe; color: #0369a1; }
        
        .main-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .main-table th { background-color: #0284c7; color: #ffffff; font-weight: bold; font-size: 10.5pt; padding: 10px 12px; border: 1px solid #0369a1; text-align: left; }
        .main-table th.th-center { text-align: center; }
        .main-table th.th-right { text-align: right; }
        
        .main-table td { padding: 8px 12px; border: 1px solid #cbd5e1; font-size: 10pt; color: #334155; }
        .main-table tr.even-row { background-color: #f8fafc; }
        .main-table tr.odd-row { background-color: #ffffff; }
        
        .cell-text { text-align: left; mso-number-format: "\@"; }
        .cell-code { text-align: left; font-family: Consolas, monospace; font-weight: bold; color: #0369a1; mso-number-format: "\@"; }
        .cell-center { text-align: center; mso-number-format: "\@"; }
        .cell-num { text-align: right; mso-number-format: "#,##0.00"; }
        .cell-status-paid { text-align: center; font-weight: bold; color: #059669; mso-number-format: "\@"; }
        
        .footer-total-title { background-color: #f1f5f9; font-weight: bold; font-size: 11pt; text-align: right; padding: 10px 12px; border-top: 2px solid #0284c7; border-bottom: 3px double #0284c7; color: #1e293b; }
        .footer-total-value { background-color: #e0f2fe; font-weight: bold; font-size: 12pt; color: #0369a1; text-align: right; padding: 10px 12px; border-top: 2px solid #0284c7; border-bottom: 3px double #0284c7; mso-number-format: "#,##0.00"; }
    </style>
</head>
<body>

    <div class="report-header">
        <div class="company-name">{{ $pharmacy }}</div>
        <div class="report-title">Sales Revenue & Transactions Report</div>
        <div class="meta-info">
            <strong>Period:</strong> {{ $from }} to {{ $to }} &nbsp;|&nbsp; 
            <strong>Generated:</strong> {{ date('d M Y, h:i A') }} &nbsp;|&nbsp; 
            <strong>Currency:</strong> {{ $currency }}
        </div>
    </div>

    <table class="kpi-table">
        <tr>
            <td class="kpi-label">Total Transactions</td>
            <td class="kpi-val" style="mso-number-format:'0';">{{ $totalTransactions }}</td>
            <td class="kpi-label">Gross Revenue ({{ $currency }})</td>
            <td class="kpi-val">{{ number_format($grossRevenue, 2) }}</td>
            <td class="kpi-label">Total Refunds ({{ $currency }})</td>
            <td class="kpi-val" style="color: #e11d48;">{{ number_format($totalRefunds, 2) }}</td>
            <td class="kpi-label kpi-highlight">Net Revenue ({{ $currency }})</td>
            <td class="kpi-val kpi-highlight">{{ number_format($totalRevenue, 2) }}</td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 140px;">Invoice #</th>
                <th class="th-center" style="width: 130px;">Date & Time</th>
                <th style="width: 160px;">Customer</th>
                <th class="th-center" style="width: 70px;">Items</th>
                <th class="th-center" style="width: 120px;">Payment Method</th>
                <th class="th-center" style="width: 90px;">Status</th>
                <th class="th-right" style="width: 120px;">Subtotal ({{ $currency }})</th>
                <th class="th-right" style="width: 100px;">Discount ({{ $currency }})</th>
                <th class="th-right" style="width: 130px;">Total Amount ({{ $currency }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $idx => $sale)
                @php
                    $rowClass = ($idx % 2 === 0) ? 'even-row' : 'odd-row';
                    $payMethod = match($sale->payment_method) {
                        'mobile_money' => 'Mobile Money',
                        'card' => 'Card',
                        default => 'Cash'
                    };
                @endphp
                <tr class="{{ $rowClass }}">
                    <td class="cell-code">{{ $sale->invoice_no }}</td>
                    <td class="cell-center">{{ $sale->created_at->format('d-M-Y H:i') }}</td>
                    <td class="cell-text">{{ $sale->customer?->name ?? 'Walk-in Customer' }}</td>
                    <td class="cell-center" style="mso-number-format:'0';">{{ $sale->items->count() }}</td>
                    <td class="cell-center">{{ $payMethod }}</td>
                    <td class="cell-status-paid">{{ ucfirst($sale->payment_status ?? 'Paid') }}</td>
                    <td class="cell-num">{{ number_format((float)($sale->subtotal ?? $sale->total), 2) }}</td>
                    <td class="cell-num" style="color: #64748b;">{{ number_format((float)($sale->discount ?? 0), 2) }}</td>
                    <td class="cell-num" style="font-weight: bold; color: #0284c7;">{{ number_format((float)$sale->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="cell-center" style="padding: 24px; color: #94a3b8;">No sales records found for the selected period.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8" class="footer-total-title">TOTAL NET REVENUE ({{ $currency }}):</td>
                <td class="footer-total-value">{{ number_format($totalRevenue, 2) }}</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
