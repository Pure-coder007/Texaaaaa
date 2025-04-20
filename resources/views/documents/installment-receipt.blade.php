<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installment Payment Receipt</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'DM Sans', Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 150px;
            max-height: 70px;
        }
        .company-info {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .receipt-title {
            text-align: center;
            margin: 20px 0;
            font-size: 20px;
            font-weight: bold;
        }
        .receipt-meta {
            margin-bottom: 20px;
        }
        .receipt-meta table {
            width: 100%;
            border-collapse: collapse;
        }
        .receipt-meta td {
            padding: 5px;
            vertical-align: top;
        }
        .client-info {
            width: 50%;
        }
        .receipt-info {
            width: 50%;
            text-align: right;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #f5f5f5;
            border-bottom: 2px solid #e0e0e0;
            padding: 10px;
            text-align: left;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        .items-table .numeric {
            text-align: right;
        }
        .totals-table {
            width: 100%;
            margin-top: 20px;
        }
        .totals-table td {
            padding: 5px;
        }
        .totals-table .label {
            text-align: right;
            font-weight: bold;
        }
        .totals-table .amount {
            text-align: right;
            width: 150px;
        }
        .grand-total {
            font-size: 16px;
            font-weight: bold;
            color: #000;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .stripe {
            background-color: #f9f9f9;
        }
        table.bordered {
            border-collapse: collapse;
        }
        table.bordered th,
        table.bordered td {
            border: 1px solid #e0e0e0;
            padding: 8px;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <table width="100%">
                <tr>
                    <td width="20%" style="vertical-align: middle;">
                        <img src="{{ public_path($companySettings['logo_path']) }}" alt="Company Logo" class="logo">
                    </td>
                    <td width="60%" style="vertical-align: middle; text-align: center;">
                        <h2 style="margin: 0;">{{ $companySettings['company_name'] }}</h2>
                        <div class="company-info">
                            {{ $companySettings['company_address'] }}<br>
                            Phone: {{ $companySettings['company_phone'] }}, WhatsApp: {{ $companySettings['company_whatsapp'] }}<br>
                            Email: {{ $companySettings['company_email'] }}<br>
                            Website: {{ $companySettings['company_website'] }}
                        </div>
                    </td>
                    <td width="20%"></td>
                </tr>
            </table>
        </div>

        <div class="receipt-title">{{ $companySettings['receipt_title'] }}</div>

        <div class="receipt-meta">
            <table>
                <tr>
                    <td class="client-info">
                        <strong>Sold to:</strong> {{ $client->name }}<br>
                        <strong>Address:</strong> {{ $client->address ?? 'N/A' }}<br>
                        <strong>Phone:</strong> {{ $client->phone }}<br>
                        <strong>Email:</strong> {{ $client->email }}
                    </td>
                    <td class="receipt-info">
                        <strong>Date:</strong> {{ $date }}<br>
                        <strong>Receipt #:</strong> {{ $receiptNumber }}<br>
                        <strong>File #:</strong> {{ $fileNumber }}<br>
                    </td>
                </tr>
            </table>
        </div>

        <table class="receipt-meta bordered" style="margin-bottom: 20px;">
            <tr style="background-color: #f5f5f5;">
                <td style="width: 20%;"><strong>PAYMENT PLAN</strong></td>
                <td style="width: 20%;"><strong>RECONCILING BANK</strong></td>
                <td style="width: 20%;"><strong>PAYMENT</strong></td>
                <td style="width: 20%;"><strong>ESTATE</strong></td>
                <td style="width: 20%;"><strong>DURATION</strong></td>
            </tr>
            <tr>
                <td>
                    {{ $installmentType }} INSTALLMENT
                </td>
                <td>
                    @if($payment && $payment->payment_method == 'bank_transfer')
                        {{ strtoupper($payment->payment_details['bank_name'] ?? 'ZENITH BANK') }}
                    @else
                        CASH
                    @endif
                </td>
                <td>
                    @if($payment && $payment->payment_method == 'bank_transfer')
                        TRANSFER
                    @else
                        CASH
                    @endif
                </td>
                <td>{{ strtoupper($purchase->estate->name) }}</td>
                <td>{{ $installmentType }}</td>
            </tr>
        </table>

        <table class="items-table bordered">
            <thead>
                <tr>
                    <th>Qty</th>
                    <th>Size (Sqm)</th>
                    <th>Description</th>
                    <th>Amount Paid</th>
                    <th>Line Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $purchase->total_plots }}</td>
                    <td>
                        @php
                            // Get the plot size from the first plot if available
                            $plotSize = '';
                            if (count($plots) > 0) {
                                $plotSize = $plots[0]->plot->area ?? '';
                            }
                        @endphp
                        {{ $plotSize }}
                    </td>
                    <td>
                        Installmental Payment for
                        {{ $purchase->total_plots > 1 ? $purchase->total_plots . ' (' . number_format($purchase->total_plots) . ') of ' : '' }}
                        {{ $plotSize }}sqm Residential plot{{ $purchase->total_plots > 1 ? 's' : '' }} of land,
                        Inclusive of Survey and Deed of Assignment @ {{ strtoupper($purchase->estate->name) }}
                    </td>
                    <td class="numeric">₦{{ number_format($payment->amount) }}</td>
                    <td class="numeric">₦{{ number_format($payment->amount) }}</td>
                </tr>
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td style="text-align: left;">
                    <strong>Amount in words:</strong> {{ strtoupper(\Illuminate\Support\Str::title(\App\Services\NumberToWords::convert($payment->amount))) }} NAIRA ONLY
                </td>
                <td style="text-align: right; width: 400px;">
                    <table width="100%">
                        <tr>
                            <td class="label">Subtotal</td>
                            <td class="amount">₦{{ number_format($payment->amount) }}</td>
                        </tr>
                        <tr>
                            <td class="label">Sales Tax</td>
                            <td class="amount"></td>
                        </tr>
                        <tr>
                            <td class="label">Total</td>
                            <td class="amount">₦{{ number_format($payment->amount) }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="height: 20px;"></td>
                        </tr>
                        <tr>
                            <td class="label grand-total">Total Amount</td>
                            <td class="amount grand-total">₦{{ number_format($purchase->total_amount) }}</td>
                        </tr>
                        <tr>
                            <td class="label">Total Paid:</td>
                            <td class="amount">₦{{ number_format($totalPaid) }}</td>
                        </tr>
                        <tr>
                            <td class="label">Outstanding Bal:</td>
                            <td class="amount">₦{{ number_format($outstandingBalance) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div style="margin-top: 20px;">
            <table width="100%">
                <tr>
                    <td style="width: 60%;">
                        <strong>FOR {{ $companySettings['company_name'] }}:</strong>
                        <br><br>
                        <div style="margin-top: 20px;">
                            <strong>{{ $companySettings['receipt_signatory_name'] }}</strong>
                            <br>
                            <div style="margin-top: 5px;">Signature</div>
                        </div>
                    </td>
                    <td style="width: 40%; text-align: center; vertical-align: bottom;">
                        <div style="border: 1px solid #aaa; padding: 5px; text-align: center;">
                            <strong>{{ $companySettings['receipt_company_name_short'] }}<br>{{ $companySettings['receipt_company_description'] }}</strong>
                            <br><br>
                            <strong>SNG..............................</strong>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>{{ $companySettings['document_footer_text'] }}</p>
        </div>
    </div>
</body>
</html>