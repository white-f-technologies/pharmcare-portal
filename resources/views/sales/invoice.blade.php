<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Receipt / Invoice') }} - {{ $sale->invoice_no }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 12px; line-height: 1.5; color: #1e293b; margin: 0; padding: 20px; background-color: #f8fafc; }
        .invoice-box { max-width: 800px; margin: auto; padding: 32px; border: 1px solid #e2e8f0; border-radius: 16px; background-color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 2px solid #059669; }
        .brand-container { display: flex; align-items: center; gap: 16px; }
        .brand-logo { max-height: 64px; max-width: 140px; object-contain: fit; }
        .company-name { font-size: 22px; font-weight: 800; color: #059669; text-transform: uppercase; tracking: -0.5px; }
        .company-info { font-size: 11px; color: #64748b; line-height: 1.4; margin-top: 4px; }
        .invoice-title-box { text-align: right; }
        .invoice-title { font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: 1px; }
        .invoice-badge { display: inline-block; padding: 3px 10px; background: #ecfdf5; color: #047857; font-weight: 700; font-size: 10px; border-radius: 9999px; margin-top: 4px; text-transform: uppercase; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 24px; background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #f1f5f9; }
        .info-box { width: 48%; }
        .info-box h4 { margin: 0 0 6px; font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-box p { margin: 0; font-size: 12px; color: #334155; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #059669; color: white; padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; }
        td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; font-size: 12px; color: #334155; }
        tr:nth-child(even) td { background-color: #fafafa; }
        .unit-tag { display: inline-block; padding: 1px 6px; background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 10px; border-radius: 4px; }
        .totals { margin-top: 24px; margin-left: auto; width: 320px; background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #f1f5f9; }
        .totals div { display: flex; justify-content: space-between; padding: 4px 0; font-size: 12px; color: #475569; }
        .totals .grand-total { font-size: 16px; font-weight: 800; border-top: 2px solid #059669; color: #059669; padding-top: 10px; margin-top: 6px; }
        .footer { margin-top: 36px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 11px; color: #64748b; }
        .footer-note { font-style: italic; font-weight: 600; color: #047857; margin-bottom: 6px; }
        .no-print { display: flex; justify-content: center; gap: 12px; margin-bottom: 20px; }
        .btn-print { padding: 10px 20px; bg: #059669; color: white; background-color: #059669; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-print:hover { background-color: #047857; }

        @media print {
            body { margin: 0; padding: 0; background: white; }
            .invoice-box { border: none; box-shadow: none; padding: 0; max-width: 100%; }
            .no-print { display: none; }
            th { background: #059669 !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .unit-tag { background: #e0f2fe !important; color: #0369a1 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">
            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            {{ __('Print Receipt / Invoice') }}
        </button>
    </div>

    <div class="invoice-box">
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

</body>
</html>