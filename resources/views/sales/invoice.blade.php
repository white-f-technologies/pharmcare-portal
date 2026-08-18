<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Receipt / Invoice') }} - {{ $sale->invoice_no }}</title>
    <style>
        /* Base Reset */
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 13px;
            line-height: 1.4;
            color: #0f172a;
            margin: 0;
            padding: 24px;
            background-color: #f1f5f9;
        }

        /* Toolbar (hidden on print) */
        .toolbar-wrapper {
            max-width: 820px;
            margin: 0 auto 20px auto;
        }
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            background: #ffffff;
            padding: 12px 18px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }
        .format-selector {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            padding: 4px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
        }
        .format-btn {
            padding: 6px 14px;
            border: none;
            background: transparent;
            color: #475569;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .format-btn:hover {
            color: #0f172a;
            background: #e2e8f0;
        }
        .format-btn.active {
            background: #0284c7;
            color: #ffffff;
            box-shadow: 0 1px 3px rgba(2, 132, 199, 0.3);
        }
        .action-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.15s ease;
        }
        .btn-primary {
            background: #0284c7;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #0369a1;
        }
        .btn-secondary {
            background: #ffffff;
            color: #475569;
            border-color: #cbd5e1;
        }
        .btn-secondary:hover {
            background: #f8fafc;
            color: #0f172a;
        }

        /* ─── FORMAT: A4 STANDARD INVOICE ─── */
        .receipt-container.format-a4 {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 36px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .format-a4 .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 2px solid #059669;
        }
        .format-a4 .brand-container { display: flex; align-items: center; gap: 16px; }
        .format-a4 .brand-logo { max-height: 64px; max-width: 140px; object-fit: contain; }
        .format-a4 .company-name { font-size: 22px; font-weight: 800; color: #059669; text-transform: uppercase; }
        .format-a4 .company-info { font-size: 11px; color: #64748b; line-height: 1.4; margin-top: 4px; }
        .format-a4 .invoice-title-box { text-align: right; }
        .format-a4 .invoice-title { font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: 1px; }
        .format-a4 .invoice-badge { display: inline-block; padding: 3px 10px; background: #ecfdf5; color: #047857; font-weight: 700; font-size: 10px; border-radius: 9999px; margin-top: 4px; text-transform: uppercase; }
        .format-a4 .info-row { display: flex; justify-content: space-between; margin-bottom: 24px; background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #f1f5f9; }
        .format-a4 .info-box { width: 48%; }
        .format-a4 .info-box h4 { margin: 0 0 6px; font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
        .format-a4 .info-box p { margin: 0; font-size: 12px; color: #334155; }
        .format-a4 table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .format-a4 th { background: #059669; color: white; padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; }
        .format-a4 td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-size: 12px; color: #334155; }
        .format-a4 tr:nth-child(even) td { background-color: #fafafa; }
        .format-a4 .unit-tag { display: inline-block; padding: 1px 6px; background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 10px; border-radius: 4px; }
        .format-a4 .totals { margin-top: 24px; margin-left: auto; width: 320px; background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #f1f5f9; }
        .format-a4 .totals div { display: flex; justify-content: space-between; padding: 4px 0; font-size: 12px; color: #475569; }
        .format-a4 .totals .grand-total { font-size: 16px; font-weight: 800; border-top: 2px solid #059669; color: #059669; padding-top: 10px; margin-top: 6px; }
        .format-a4 .footer { margin-top: 36px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 11px; color: #64748b; }
        .format-a4 .footer-note { font-style: italic; font-weight: 600; color: #047857; margin-bottom: 6px; }

        /* ─── FORMAT: THERMAL 80MM & 58MM (MINI POS PRINTERS) ─── */
        .receipt-container.format-thermal80,
        .receipt-container.format-thermal58 {
            background: #ffffff;
            margin: 0 auto;
            padding: 16px 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            font-family: 'Courier New', Courier, Consolas, Monaco, monospace;
            color: #000000;
        }

        /* 80mm Width */
        .receipt-container.format-thermal80 {
            width: 80mm;
            max-width: 80mm;
            font-size: 12px;
        }

        /* 58mm Width (Mini Thermal) */
        .receipt-container.format-thermal58 {
            width: 58mm;
            max-width: 58mm;
            padding: 10px 5px;
            font-size: 10px;
            line-height: 1.25;
        }

        .thermal-center { text-align: center; }
        .thermal-bold { font-weight: bold; }
        .thermal-title {
            font-size: 15px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: -0.5px;
            margin-bottom: 2px;
        }
        .format-thermal58 .thermal-title {
            font-size: 12.5px;
        }
        .thermal-subtitle {
            font-size: 11px;
            line-height: 1.3;
            margin-bottom: 4px;
        }
        .format-thermal58 .thermal-subtitle {
            font-size: 9px;
        }
        .thermal-divider {
            border: none;
            border-top: 1px dashed #000000;
            margin: 6px 0;
        }
        .thermal-double-divider {
            border: none;
            border-top: 2px solid #000000;
            margin: 6px 0;
        }
        .thermal-meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 2px;
        }
        .format-thermal58 .thermal-meta-row {
            font-size: 9px;
        }
        
        /* Thermal Items Table */
        .thermal-table {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0;
        }
        .thermal-table th {
            text-align: left;
            font-size: 11px;
            border-bottom: 1px dashed #000000;
            padding: 3px 0;
            font-weight: bold;
        }
        .format-thermal58 .thermal-table th {
            font-size: 9px;
        }
        .thermal-table td {
            padding: 4px 0;
            font-size: 11px;
            vertical-align: top;
        }
        .format-thermal58 .thermal-table td {
            font-size: 9px;
            padding: 2.5px 0;
        }
        .thermal-item-name {
            font-weight: bold;
            word-break: break-word;
        }
        .thermal-item-sub {
            font-size: 9.5px;
            color: #333333;
        }
        .format-thermal58 .thermal-item-sub {
            font-size: 8px;
        }
        .thermal-totals {
            margin-top: 4px;
        }
        .thermal-total-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            font-size: 11.5px;
        }
        .format-thermal58 .thermal-total-row {
            font-size: 9.5px;
        }
        .thermal-grand-total {
            font-size: 14px;
            font-weight: 900;
            padding: 4px 0;
        }
        .format-thermal58 .thermal-grand-total {
            font-size: 11.5px;
        }
        .thermal-footer {
            margin-top: 10px;
            text-align: center;
            font-size: 10px;
            line-height: 1.3;
        }
        .format-thermal58 .thermal-footer {
            font-size: 8.5px;
        }
        .barcode-box {
            text-align: center;
            margin: 8px 0 4px 0;
        }

        /* ─── PRINT MEDIA STYLES ─── */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                color: #000000 !important;
            }
            .no-print {
                display: none !important;
            }
            .receipt-container {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
            }

            /* A4 Print */
            body.print-a4 .receipt-container.format-a4 {
                display: block !important;
                max-width: 100% !important;
                padding: 10mm !important;
            }
            body.print-a4 #receipt-thermal {
                display: none !important;
            }

            /* 80mm Thermal Print */
            body.print-thermal80 .receipt-container.format-thermal80 {
                display: block !important;
                width: 76mm !important;
                max-width: 76mm !important;
                padding: 2mm 1mm !important;
            }
            body.print-thermal80 #receipt-a4 {
                display: none !important;
            }

            /* 58mm Mini Thermal Print */
            body.print-thermal58 .receipt-container.format-thermal58 {
                display: block !important;
                width: 50mm !important;
                max-width: 50mm !important;
                padding: 1mm 0.5mm !important;
                font-size: 9.5px !important;
            }
            body.print-thermal58 #receipt-a4 {
                display: none !important;
            }

            th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body class="print-thermal80">

    <!-- Top Floating Toolbar (Hidden on Print) -->
    <div class="toolbar-wrapper no-print">
        <div class="toolbar">
            <div class="format-selector">
                <button type="button" class="format-btn active" id="btn-80" onclick="setFormat('thermal80')">
                    <svg class="w-3.5 h-3.5 inline mr-1 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>80mm POS Thermal</span>
                </button>
                <button type="button" class="format-btn" id="btn-58" onclick="setFormat('thermal58')">
                    <svg class="w-3.5 h-3.5 inline mr-1 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>58mm Mini Thermal</span>
                </button>
                <button type="button" class="format-btn" id="btn-a4" onclick="setFormat('a4')">
                    <svg class="w-3.5 h-3.5 inline mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>A4 Standard Invoice</span>
                </button>
            </div>

            <div class="action-group">
                <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                    ← {{ __('Back to Sales') }}
                </a>
                <button onclick="triggerPrint()" class="btn btn-primary">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    {{ __('Print Receipt') }}
                </button>
            </div>
        </div>
    </div>

    <!-- ─── 1. THERMAL 80MM / 58MM RECEIPT TEMPLATE ─── -->
    <div id="receipt-thermal" class="receipt-container format-thermal80">
        <div class="thermal-center">
            @if(setting('system_logo') && \Illuminate\Support\Facades\Storage::disk('public')->exists(setting('system_logo')))
                <img src="{{ asset('media/' . setting('system_logo')) }}" alt="Logo" style="max-height: 44px; max-width: 110px; margin-bottom: 4px; filter: grayscale(100%);">
            @endif
            <div class="thermal-title">{{ setting('system_name', setting('app_name', config('app.name', 'PharmCare'))) }}</div>
            <div class="thermal-subtitle">
                @if(setting('address')) {{ setting('address') }}<br> @endif
                @if(setting('contact_phone')) Tel: {{ setting('contact_phone') }} @endif
                @if(setting('tax_number')) <br>TIN: {{ setting('tax_number') }} @endif
            </div>
        </div>

        <div class="thermal-double-divider"></div>

        <div class="thermal-center thermal-bold" style="font-size: 12.5px; text-transform: uppercase; margin-bottom: 4px;">
            {{ __('SALES RECEIPT') }}
        </div>

        <div class="thermal-meta-row">
            <span><strong>{{ __('Invoice #') }}:</strong> {{ $sale->invoice_no }}</span>
            <span>{{ $sale->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="thermal-meta-row">
            <span><strong>{{ __('Cashier') }}:</strong> {{ $sale->user?->name ?? 'System' }}</span>
            <span><strong>{{ __('Pay') }}:</strong> {{ strtoupper($sale->payment_method) }}</span>
        </div>
        <div class="thermal-meta-row">
            <span><strong>{{ __('Customer') }}:</strong> {{ $sale->customer?->name ?? __('Walk-in Customer') }}</span>
            @if($sale->customer && $sale->customer->phone)
                <span>{{ $sale->customer->phone }}</span>
            @endif
        </div>

        <div class="thermal-divider"></div>

        <table class="thermal-table">
            <thead>
                <tr>
                    <th style="width: 50%;">{{ __('ITEM') }}</th>
                    <th style="width: 20%; text-align: center;">{{ __('QTY') }}</th>
                    <th style="width: 30%; text-align: right;">{{ __('TOTAL') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                    <tr>
                        <td>
                            <div class="thermal-item-name">{{ $item->medicine?->name }}</div>
                            <div class="thermal-item-sub">
                                {{ setting('currency_symbol', 'UGX') }} {{ format_price($item->unit_price) }} / {{ $item->unit_name ?? $item->medicine?->base_unit ?? 'Unit' }}
                                @if($item->batch) • B: {{ $item->batch->batch_number }} @endif
                            </div>
                        </td>
                        <td style="text-align: center; font-weight: bold;">
                            {{ $item->unit_quantity ?? $item->quantity }} {{ $item->unit_name ?? $item->medicine?->base_unit ?? '' }}
                        </td>
                        <td style="text-align: right; font-weight: bold;">
                            {{ format_price(($item->unit_quantity ?? 1) * $item->unit_price) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="thermal-divider"></div>

        <div class="thermal-totals">
            <div class="thermal-total-row">
                <span>{{ __('Subtotal') }}:</span>
                <span>{{ setting('currency_symbol', 'UGX') }} {{ format_price($sale->subtotal) }}</span>
            </div>
            @if($sale->tax > 0)
                <div class="thermal-total-row">
                    <span>{{ __('Tax / VAT') }}:</span>
                    <span>{{ setting('currency_symbol', 'UGX') }} {{ format_price($sale->tax) }}</span>
                </div>
            @endif
            @if($sale->discount > 0)
                <div class="thermal-total-row">
                    <span>{{ __('Discount') }}:</span>
                    <span>-{{ setting('currency_symbol', 'UGX') }} {{ format_price($sale->discount) }}</span>
                </div>
            @endif
            <div class="thermal-double-divider"></div>
            <div class="thermal-total-row thermal-grand-total">
                <span>{{ __('TOTAL PAID') }}:</span>
                <span>{{ setting('currency_symbol', 'UGX') }} {{ format_price($sale->total) }}</span>
            </div>
            <div class="thermal-double-divider"></div>
        </div>

        <div class="barcode-box">
            <div style="font-size: 10px; font-weight: bold; letter-spacing: 1px;">* {{ $sale->invoice_no }} *</div>
        </div>

        <div class="thermal-footer">
            <div class="thermal-bold">{{ setting('receipt_footer', 'Thank you for shopping with us! Get well soon.') }}</div>
            <div style="margin-top: 3px; font-size: 8.5px; color: #555555;">
                {{ setting('system_name', 'PharmCare') }} • Powered by White F Technologies
            </div>
        </div>
    </div>

    <!-- ─── 2. A4 STANDARD INVOICE TEMPLATE (Shown when A4 format is active) ─── -->
    <div id="receipt-a4" class="receipt-container format-a4" style="display: none;">
        <div class="header">
            <div class="brand-container">
                @if(setting('system_logo') && \Illuminate\Support\Facades\Storage::disk('public')->exists(setting('system_logo')))
                    <img src="{{ asset('media/' . setting('system_logo')) }}" alt="Logo" class="brand-logo">
                @endif
                <div>
                    <div class="company-name">{{ setting('system_name', setting('app_name', config('app.name', 'PharmCare'))) }}</div>
                    <div class="company-info">
                        @if(setting('address')) {{ setting('address') }}<br> @endif
                        @if(setting('contact_phone')) Phone: {{ setting('contact_phone') }} @endif
                        @if(setting('contact_email')) | Email: {{ setting('contact_email') }} @endif
                        @if(setting('tax_number')) <br><strong>TIN / Tax ID:</strong> {{ setting('tax_number') }} @endif
                    </div>
                </div>
            </div>
            <div class="invoice-title-box">
                <div class="invoice-title">{{ __('RECEIPT') }}</div>
                <div class="invoice-badge">{{ ucfirst($sale->payment_method) }} {{ __('Paid') }}</div>
            </div>
        </div>

        <div class="info-row">
            <div class="info-box">
                <h4>{{ __('Customer Info') }}</h4>
                <p><strong>{{ $sale->customer?->name ?? __('Walk-in Customer') }}</strong></p>
                @if($sale->customer)
                    <p>{{ $sale->customer->phone ?? '' }} {{ $sale->customer->email ? '• ' . $sale->customer->email : '' }}</p>
                @endif
            </div>
            <div class="info-box" style="text-align:right;">
                <h4>{{ __('Transaction Details') }}</h4>
                <p><strong>{{ __('Invoice #') }}:</strong> {{ $sale->invoice_no }}</p>
                <p><strong>{{ __('Date') }}:</strong> {{ $sale->created_at->format('Y-m-d H:i') }}</p>
                <p><strong>{{ __('Cashier') }}:</strong> {{ $sale->user?->name ?? 'System' }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th style="width:45%;">{{ __('Item & Description') }}</th>
                    <th style="width:20%;text-align:center;">{{ __('Quantity & Unit') }}</th>
                    <th style="width:15%;text-align:right;">{{ __('Unit Price') }}</th>
                    <th style="width:15%;text-align:right;">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->medicine?->name }}</strong>
                            @if($item->batch)
                                <div style="font-size:10px;color:#64748b;">Batch: {{ $item->batch->batch_number }}</div>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <strong>{{ $item->unit_quantity ?? $item->quantity }}</strong>
                            <span class="unit-tag">{{ $item->unit_name ?? $item->medicine?->base_unit ?? 'Unit' }}</span>
                        </td>
                        <td style="text-align:right;">{{ setting('currency_symbol', 'UGX') }} {{ format_price($item->unit_price) }}</td>
                        <td style="text-align:right;font-weight:700;">{{ setting('currency_symbol', 'UGX') }} {{ format_price(($item->unit_quantity ?? 1) * $item->unit_price) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div>
                <span>{{ __('Subtotal') }}</span>
                <span>{{ setting('currency_symbol', 'UGX') }} {{ format_price($sale->subtotal) }}</span>
            </div>
            @if($sale->tax > 0)
                <div>
                    <span>{{ __('Tax') }}</span>
                    <span>{{ setting('currency_symbol', 'UGX') }} {{ format_price($sale->tax) }}</span>
                </div>
            @endif
            @if($sale->discount > 0)
                <div>
                    <span>{{ __('Discount') }}</span>
                    <span>-{{ setting('currency_symbol', 'UGX') }} {{ format_price($sale->discount) }}</span>
                </div>
            @endif
            <div class="grand-total">
                <span>{{ __('Grand Total') }}</span>
                <span>{{ setting('currency_symbol', 'UGX') }} {{ format_price($sale->total) }}</span>
            </div>
        </div>

        <div class="footer">
            <div class="footer-note">{{ setting('receipt_footer', 'Thank you for shopping with us! Get well soon.') }}</div>
            <div>{{ setting('system_name', 'PharmCare') }} • {{ __('powered by whiteFtechnologies') }}</div>
        </div>
    </div>

    <!-- Script to handle Format switching, persistence, and thermal printing -->
    <script>
        function setFormat(format) {
            // Update buttons
            document.getElementById('btn-80').classList.remove('active');
            document.getElementById('btn-58').classList.remove('active');
            document.getElementById('btn-a4').classList.remove('active');

            const thermalContainer = document.getElementById('receipt-thermal');
            const a4Container = document.getElementById('receipt-a4');
            document.body.className = '';

            if (format === 'thermal58') {
                document.getElementById('btn-58').classList.add('active');
                thermalContainer.style.display = 'block';
                thermalContainer.className = 'receipt-container format-thermal58';
                a4Container.style.display = 'none';
                document.body.classList.add('print-thermal58');
                localStorage.setItem('pharmcare_receipt_format', 'thermal58');
            } else if (format === 'a4') {
                document.getElementById('btn-a4').classList.add('active');
                thermalContainer.style.display = 'none';
                a4Container.style.display = 'block';
                document.body.classList.add('print-a4');
                localStorage.setItem('pharmcare_receipt_format', 'a4');
            } else {
                // Default 80mm
                document.getElementById('btn-80').classList.add('active');
                thermalContainer.style.display = 'block';
                thermalContainer.className = 'receipt-container format-thermal80';
                a4Container.style.display = 'none';
                document.body.classList.add('print-thermal80');
                localStorage.setItem('pharmcare_receipt_format', 'thermal80');
            }
        }

        function triggerPrint() {
            window.print();
        }

        // Initialize from URL params or localStorage
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const formatParam = urlParams.get('format');
            const autoPrint = urlParams.get('autoprint');
            const savedFormat = localStorage.getItem('pharmcare_receipt_format') || 'thermal80';

            const activeFormat = formatParam || savedFormat;
            setFormat(activeFormat);

            if (autoPrint === '1' || autoPrint === 'true') {
                setTimeout(() => {
                    window.print();
                }, 300);
            }
        });

        // Hotkey: Ctrl+P triggers print
        window.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                triggerPrint();
            }
        });
    </script>
</body>
</html>