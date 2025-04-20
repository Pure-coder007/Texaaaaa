<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $companySettings['receipt_title'] }}</title>
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
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #e0e0e0;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .promo-bonus {
            background-color: #f9f9f9;
            font-style: italic;
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

        <table class="receipt-meta" style="margin-bottom: 20px;">
            <tr>
                <td style="width: 25%;"><strong>PAYMENT PLAN</strong></td>
                <td style="width: 25%;"><strong>RECONCILING BANK</strong></td>
                <td style="width: 25%;"><strong>PAYMENT</strong></td>
                <td style="width: 25%;"><strong>ESTATE</strong></td>
            </tr>
            <tr>
                <td>
                    @switch($purchase->payment_plan_type)
                        @case('outright')
                            OUTRIGHT
                            @break
                        @case('six_month')
                            6-MONTH INSTALLMENT
                            @break
                        @case('twelve_month')
                            12-MONTH INSTALLMENT
                            @break
                        @default
                            {{ strtoupper($purchase->payment_plan_type) }}
                    @endswitch
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
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Qty</th>
                    <th>Size (Sqm)</th>
                    <th>Description</th>
                    <th>Amount Paid</th>
                    {{-- <th>Instal. Per Plot</th> --}}
                    <th>Discount</th>
                    <th>Line Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Group plots by type, area, and attributes
                    $groupedPlots = collect($plots)
                        ->groupBy(function($plot) {
                            return $plot->plotType->name .
                                '_' . $plot->plot->area .
                                '_' . $plot->is_commercial .
                                '_' . $plot->is_corner .
                                '_' . $plot->is_promo_bonus;
                        });

                    $totalQty = 0;
                    $totalArea = 0;
                    $totalAmount = 0;

                    // Count how many types of plots we have
                    $regularPlotsCount = 0;
                    $commercialPlotsCount = 0;
                    $cornerPlotsCount = 0;
                    $promoBonusPlotsCount = 0;

                    foreach($plots as $plot) {
                        if ($plot->is_promo_bonus) {
                            $promoBonusPlotsCount++;
                        } else if ($plot->is_commercial && $plot->is_corner) {
                            $commercialPlotsCount++;
                            $cornerPlotsCount++;
                        } else if ($plot->is_commercial) {
                            $commercialPlotsCount++;
                        } else if ($plot->is_corner) {
                            $cornerPlotsCount++;
                        } else {
                            $regularPlotsCount++;
                        }
                    }

                    // Create a single consolidated item
                    $totalPlotQty = $plots->count();
                    $totalRegularQty = $totalPlotQty - $promoBonusPlotsCount;

                    // Get the first plot for reference
                    $firstPlot = $plots->first();
                    $groupArea = $firstPlot ? $firstPlot->plot->area : 0;

                    $totalPaidAmount = $payment->amount;

                @endphp

                <!-- Single consolidated row for all plots -->
                <tr>
                    <td>{{ $totalRegularQty }}</td>
                    <td>{{ $groupArea }}</td>
                    <td>
                        @if($purchase->payment_plan_type === 'outright')
                            Full payment for {{ $groupArea }}sqm {{ $firstPlot->plotType->name }} plots of land
                            @if($commercialPlotsCount > 0 || $cornerPlotsCount > 0)
                                (includes
                                @if($commercialPlotsCount > 0)
                                    {{ $commercialPlotsCount }} Commercial
                                @endif
                                @if($commercialPlotsCount > 0 && $cornerPlotsCount > 0)
                                    ,
                                @endif
                                @if($cornerPlotsCount > 0)
                                    {{ $cornerPlotsCount }} Corner
                                @endif
                                plot{{ ($commercialPlotsCount + $cornerPlotsCount) > 1 ? 's' : '' }})
                            @endif
                            {{-- @if($promoBonusPlotsCount > 0)
                                , includes {{ $promoBonusPlotsCount }} FREE BONUS PROMO plot{{ $promoBonusPlotsCount > 1 ? 's' : '' }}
                            @endif --}}
                            , Deed of Payment, Survey Plan, Plot Demarcation (inclusive)<br>
                            @ {{ strtoupper($purchase->estate->name) }}
                        @else
                            Installment payment for {{ $groupArea }}sqm {{ $firstPlot->plotType->name }} plots of land
                            @if($commercialPlotsCount > 0 || $cornerPlotsCount > 0)
                                (includes
                                @if($commercialPlotsCount > 0)
                                    {{ $commercialPlotsCount }} Commercial
                                @endif
                                @if($commercialPlotsCount > 0 && $cornerPlotsCount > 0)
                                    ,
                                @endif
                                @if($cornerPlotsCount > 0)
                                    {{ $cornerPlotsCount }} Corner
                                @endif
                                plot{{ ($commercialPlotsCount + $cornerPlotsCount) > 1 ? 's' : '' }})
                            @endif

                            <br>@ {{ strtoupper($purchase->estate->name) }}
                        @endif
                    </td>
                    <td class="numeric">₦{{ number_format($totalPaidAmount) }}</td>
                    <td class="numeric">
                        @if($purchase->promo_code_id)
                            @php
                                $promoCode = \App\Models\PromoCode::find($purchase->promo_code_id);
                                if($promoCode) {
                                    if($promoCode->discount_type === 'percentage') {
                                        echo $promoCode->discount_amount . '%';
                                    } else {
                                        echo '₦' . number_format($promoCode->discount_amount);
                                    }
                                }
                            @endphp
                        @endif
                    </td>
                    <td class="numeric">₦{{ number_format($totalPaidAmount) }}</td>
                </tr>

                <!-- Only display promo bonus plots separately if needed -->
                @if($promoBonusPlotsCount > 0 && $purchase->payment_plan_type === 'outright')
                    <tr class="promo-bonus">
                        <td>{{ $promoBonusPlotsCount }}</td>
                        <td>{{ $groupArea }}</td>
                        <td>
                            FREE BONUS plot{{ $promoBonusPlotsCount > 1 ? 's' : '' }} of land<br>
                            @ {{ strtoupper($purchase->estate->name) }} (PROMO BONUS)
                        </td>
                        <td class="numeric">FREE</td>
                        <td class="numeric"></td>
                        <td class="numeric">FREE</td>
                    </tr>
                @endif
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
                            <td class="amount">₦{{ number_format($purchase->base_price + $purchase->premium_amount) }}</td>
                        </tr>
                        @if($purchase->promo_code_id)
                            <tr>
                                <td class="label">Discount</td>
                                <td class="amount">-₦{{ number_format(($purchase->base_price + $purchase->premium_amount) - $purchase->total_amount) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="label">Sales Tax</td>
                            <td class="amount"></td>
                        </tr>
                        <tr>
                            <td class="label">Total</td>
                            <td class="amount">₦{{ number_format($purchase->total_amount) }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="height: 20px;"></td>
                        </tr>
                        <tr>
                            <td class="label grand-total">Total Amount</td>
                            <td class="amount grand-total">₦{{ number_format($purchase->total_amount) }}</td>
                        </tr>
                        <tr>
                            <td class="label">Outstanding Bal:</td>
                            <td class="amount">
                                @if($payment)
                                    ₦{{ number_format(max(0, $purchase->total_amount - $payment->amount)) }}
                                @else
                                    ₦{{ number_format($purchase->total_amount) }}
                                @endif
                            </td>
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
