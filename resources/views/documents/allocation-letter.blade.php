<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $companySettings['allocation_letter_title'] }}</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        @page {
            margin: 0;
            size: A4 portrait;
        }
        body {
            font-family: 'DM Sans', Arial, sans-serif;
            margin: 0;
            padding: 20px 40px;
            color: #333;
            line-height: 1.5;
            background-color: #fff;
        }
        .container {
            max-width: 100%;
            margin: 0 auto;
        }
        .letterhead {
            margin-bottom: 30px;
        }
        .logo {
            max-height: 60px;
            display: block;
        }
        .letterhead-info {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        .date {
            margin-top: 40px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .client-info {
            margin-top: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .greeting {
            margin-top: 20px;
        }
        .letter-title {
            font-weight: bold;
            color: #2c5282;
            margin: 15px 0;
            text-transform: uppercase;
            font-size: 16px;
        }
        .allocation-details {
            margin-bottom: 20px;
        }
        .allocation-table {
            width: 100%;
            margin: 20px 0;
            border-spacing: 0;
        }
        .allocation-table tr {
            height: 40px;
        }
        .allocation-table tr td:first-child {
            font-weight: bold;
            color: #333;
            width: 35%;
            vertical-align: top;
        }
        .allocation-table tr td:last-child {
            border-bottom: 1px solid #ccc;
            padding-left: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .note {
            font-weight: bold;
            margin: 20px 0;
        }
        .footer-text {
            margin-top: 20px;
        }
        .company-name {
            font-weight: bold;
            text-transform: uppercase;
        }
        .social-info {
            margin-top: 25px;
            font-size: 12px;
        }
        .social-info p {
            margin: 2px 0;
        }
        .social-link {
            font-weight: bold;
        }
        .official-address {
            margin-top: 10px;
            font-weight: bold;
        }
        .admin-contact {
            margin-top: 5px;
        }
        .email-website {
            text-align: right;
            font-size: 11px;
            margin-top: 40px;
        }
        .promo-line {
            margin-top: 30px;
            font-weight: bold;
            font-style: italic;
            font-size: 15px;
            color: #2c5282;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="letterhead">
            <img src="{{ public_path($companySettings['logo_path']) }}" alt="Company Logo" class="logo">
            <div class="letterhead-info">
                {{ $companySettings['company_address'] }}<br>
                ☎+234 {{ $companySettings['company_phone'] }}
            </div>
        </div>

        <div class="date">{{ now()->format('jS F, Y') }}</div>

        <div class="client-info">
            {{ strtoupper($client->name) }}<br>
            {{ strtoupper($client->address ?? '') }}<br>
            {{ $client->phone }}
        </div>

        <div class="greeting">Dear Valued Client,</div>

        <div class="letter-title">{{ $companySettings['allocation_letter_title'] }}</div>

        <div class="allocation-details">
            You are hereby notified of your allocation as follows:
        </div>

        <table class="allocation-table">
            <tr>
                <td>Estate Name:</td>
                <td>{{ strtoupper($purchase->estate->name) }}</td>
            </tr>
            <tr>
                <td>Type of Plot:</td>
                <td>
                @php
                    $plotTypes = $plots->map(function($p) {
                        return strtoupper($p->plotType->name ?? 'RESIDENTIAL');
                    })->unique()->join(', ');
                @endphp
                {{ $plotTypes }}
                </td>
            </tr>
            <tr>
                <td>No of Plots:</td>
                <td>
                @if(count($plots) > 1)
                    {{ strtoupper(App\Services\NumberToWords::convert(count($plots))) }} ({{ count($plots) }}) {{ $purchase->total_area }}SQM PLOTS
                    @if($purchase->free_plots > 0)
                        PLUS {{ strtoupper(App\Services\NumberToWords::convert($purchase->free_plots)) }} ({{ $purchase->free_plots }}) FREE
                        @if(isset($plots->first()->plotType))
                            {{ $plots->first()->plotType->size_sqm }}SQM
                        @else
                            300SQM
                        @endif
                    @endif
                @else
                    ONE (1) PLOT OF {{ $plots->first()->plot->area ?? '464.00' }}SQM
                @endif
                </td>
            </tr>
            <tr>
                <td>Plot Details:</td>
                <td>
                @if(count($plots) > 1)
                    @php
                        // Group plots by type and size instead of plot number
                        $plotCategories = $plots->groupBy(function($plot) {
                            $type = $plot->is_commercial ? 'COMMERCIAL' : 'RESIDENTIAL';
                            $cornerType = $plot->is_corner ? 'CORNER' : 'STANDARD';
                            $size = $plot->area;
                            return "{$type} {$cornerType} {$size}";
                        });

                        $plotDescription = $plotCategories->map(function($group, $category) {
                            $parts = explode(' ', $category);
                            $type = $parts[0];
                            $cornerType = $parts[1];
                            $size = $parts[2];

                            return "{$type} " . ($cornerType == 'CORNER' ? 'CORNER ' : '') .
                                   "({$size}SQM) x " . count($group);
                        })->join(', ');
                    @endphp
                    {{ $plotDescription }}
                @else
                    @php
                        $plot = $plots->first()->plot;
                        $type = $plot->is_commercial ? 'COMMERCIAL' : 'RESIDENTIAL';
                        $cornerType = $plot->is_corner ? 'CORNER' : 'STANDARD';
                    @endphp
                    {{ $type }} {{ $cornerType == 'CORNER' ? 'CORNER' : '' }} PLOT ({{ $plot->area }}SQM)
                @endif
                </td>
            </tr>
            <tr>
                <td>Date of Allocation:</td>
                <td>{{ now()->format('jS F, Y') }}</td>
            </tr>
        </table>

        <div class="note">NB: {{ $companySettings['allocation_note'] }}</div>

        <div class="footer-text">
            {{ $companySettings['allocation_footer_text'] }}
        </div>

        <div class="footer-text">
            Thanking you for choosing <span class="company-name">{{ $companySettings['company_name'] }} {{ $companySettings['receipt_company_description'] }}</span>.
        </div>

        <div class="social-info">
            <p>For more information, visit us at:</p>
            <p>Instagram <span class="social-link">@pwan_champion</span></p>
            <p>Facebook <span class="social-link">@pwan.champion</span></p>
            <p><span class="social-link">www.pwanchampion.com</span></p>
            <p class="official-address">Official Address: {{ $companySettings['company_address'] }}</p>
            <p class="admin-contact">Admin Officer: {{ $companySettings['company_phone'] }}</p>
        </div>



        <div class="email-website">
            {{ $companySettings['company_email'] }} ✦ {{ $companySettings['company_website'] }}
        </div>
    </div>
</body>
</html>