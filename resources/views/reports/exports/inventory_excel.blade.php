<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inventory Valuation Report</title>
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
        .cell-bold { text-align: left; font-weight: bold; color: #0f172a; mso-number-format: "\@"; }
        .cell-center { text-align: center; mso-number-format: "\@"; }
        .cell-num { text-align: right; mso-number-format: "#,##0.00"; }
        .cell-qty { text-align: center; font-weight: bold; mso-number-format: "#,##0"; }
        
        .footer-total-title { background-color: #f1f5f9; font-weight: bold; font-size: 11pt; text-align: right; padding: 10px 12px; border-top: 2px solid #0284c7; border-bottom: 3px double #0284c7; color: #1e293b; }
        .footer-total-value { background-color: #e0f2fe; font-weight: bold; font-size: 12pt; color: #0369a1; text-align: right; padding: 10px 12px; border-top: 2px solid #0284c7; border-bottom: 3px double #0284c7; mso-number-format: "#,##0.00"; }
    </style>
</head>
<body>

    <div class="report-header">
        <div class="company-name">{{ $pharmacy }}</div>
        <div class="report-title">Inventory Valuation & Stock Status Report</div>
        <div class="meta-info">
            <strong>Generated On:</strong> {{ date('d M Y, h:i A') }} &nbsp;|&nbsp; 
            <strong>Currency:</strong> {{ $currency }}
        </div>
    </div>

    <table class="kpi-table">
        <tr>
            <td class="kpi-label">Total Medicines</td>
            <td class="kpi-val" style="mso-number-format:'0';">{{ $totalMedicines }}</td>
            <td class="kpi-label">Total Physical Stock</td>
            <td class="kpi-val" style="mso-number-format:'0';">{{ number_format($totalStockQty) }}</td>
            <td class="kpi-label">Stock Valuation Cost ({{ $currency }})</td>
            <td class="kpi-val">{{ number_format($totalCostValue, 2) }}</td>
            <td class="kpi-label kpi-highlight">Stock Valuation Retail ({{ $currency }})</td>
            <td class="kpi-val kpi-highlight">{{ number_format($totalRetailValue, 2) }}</td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 180px;">Medicine Name</th>
                <th style="width: 150px;">Generic Name</th>
                <th style="width: 130px;">Category</th>
                <th class="th-center" style="width: 70px;">Unit</th>
                <th class="th-center" style="width: 80px;">Stock Qty</th>
                <th class="th-center" style="width: 80px;">Reorder</th>
                <th class="th-center" style="width: 95px;">Status</th>
                <th class="th-right" style="width: 120px;">Avg Cost ({{ $currency }})</th>
                <th class="th-right" style="width: 120px;">Selling Price ({{ $currency }})</th>
                <th class="th-right" style="width: 135px;">Cost Value ({{ $currency }})</th>
                <th class="th-right" style="width: 135px;">Retail Value ({{ $currency }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($medicines as $idx => $med)
                @php
                    $rowClass = ($idx % 2 === 0) ? 'even-row' : 'odd-row';
                    $statusColor = match($med->status_label) {
                        'Out of Stock' => '#e11d48',
                        'Low Stock' => '#d97706',
                        default => '#059669'
                    };
                @endphp
                <tr class="{{ $rowClass }}">
                    <td class="cell-bold">{{ $med->name }}</td>
                    <td class="cell-text" style="color: #64748b;">{{ $med->generic_name ?? '—' }}</td>
                    <td class="cell-text">{{ $med->category?->name ?? 'Uncategorized' }}</td>
                    <td class="cell-center">{{ $med->base_unit ?? 'Unit' }}</td>
                    <td class="cell-qty">{{ $med->total_stock }}</td>
                    <td class="cell-center" style="mso-number-format:'0'; color: #64748b;">{{ $med->reorder_level }}</td>
                    <td class="cell-center" style="font-weight: bold; color: {{ $statusColor }};">{{ $med->status_label }}</td>
                    <td class="cell-num">{{ number_format($med->avg_purchase_price, 2) }}</td>
                    <td class="cell-num">{{ number_format($med->avg_selling_price, 2) }}</td>
                    <td class="cell-num" style="color: #475569;">{{ number_format($med->stock_cost_value, 2) }}</td>
                    <td class="cell-num" style="font-weight: bold; color: #0284c7;">{{ number_format($med->stock_retail_value, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="cell-center" style="padding: 24px; color: #94a3b8;">No medicines found matching criteria.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="footer-total-title">TOTAL PHYSICAL STOCK:</td>
                <td class="footer-total-value" style="text-align: center; mso-number-format:'#,##0';">{{ number_format($totalStockQty) }}</td>
                <td colspan="4" class="footer-total-title">TOTAL VALUATION ({{ $currency }}):</td>
                <td class="footer-total-value">{{ number_format($totalCostValue, 2) }}</td>
                <td class="footer-total-value">{{ number_format($totalRetailValue, 2) }}</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
