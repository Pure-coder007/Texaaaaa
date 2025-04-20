<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $companySettings['contract_title'] }}</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'DM Sans', Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #000;
            line-height: 1.5;
            font-size: 12pt;
        }
        .page-container {
            border: 2px solid #000;
            margin: 10px;
            padding: 40px;
            position: relative;
            min-height: 1050px;
            box-sizing: border-box;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 120px;
            max-height: 50px;
            display: block;
            margin: 0 auto;
        }
        .title {
            text-align: center;
            font-size: 20pt;
            font-weight: bold;
            margin: 20px 0 30px;
        }
        .section-header {
            text-align: center;
            font-weight: bold;
            margin: 20px 0;
            font-size: 14pt;
        }
        .parties {
            text-align: center;
            margin: 20px 0;
        }
        .party {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 20pt;
        }
        .party-label {
            font-style: italic;
            margin-top: 5px;
            font-size: 20pt;
        }
        .property-box {
            border: 1px solid #000;
            padding: 15px;
            text-align: center;
            margin: 25px 0;
            font-weight: bold;
            font-size: 16pt;
            text-transform: uppercase;
        }
        .prepared-by {
            margin-top: 30px;
            margin-bottom: 20px;
            font-size: 16pt;
            font-weight: 500;
        }
        .agreement-date {
            font-weight: bold;
            margin: 25px 0 15px;
        }
        p {
            margin: 10px 0;
        }
        .clause {
            margin: 10px 0;
        }
        .clause-number {
            font-weight: bold;
        }
        .subclauses {
            padding-left: 30px;
        }
        .witness-section {
            text-transform: uppercase;
            margin-top: 30px;
        }
        .signature-block {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            width: 100%;
            display: inline-block;
            margin: 5px 0;
            height: 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
        }
        .footer-logo {
            max-height: 30px;
        }
        .page-break {
            page-break-after: always;
        }
        strong {
            font-weight: bold;
        }
        .small-logo {
            max-height: 20px;
            margin: 0 auto;
            display: block;
        }
    </style>
</head>
<body>
    <!-- Page 1 -->
    <div class="page-container">
        <div class="header">
            <img src="{{ public_path($companySettings['logo_path']) }}" alt="Company Logo" class="logo">
        </div>

        <div class="title">CONTRACT OF SALE</div>

        <div class="section-header">BETWEEN</div>

        <div class="parties">
            <div class="party">PWAN CHAMPION REALTORS AND ESTATES<br>LIMITED</div>
            <div class="party-label">(THE VENDOR)</div>
        </div>

        <div class="section-header">AND</div>

        <div class="parties">
            <div class="party">{{ strtoupper($client->name) }}</div>
            <div class="party-label">(THE PURCHASER)</div>
        </div>

        <div class="property-box">
            IN RESPECT OF
            @if(count($plots) > 1)
                {{ count($plots) }} ({{ App\Services\NumberToWords::convert(count($plots)) }})
                PLOTS OF LAND AT {{ strtoupper($purchase->estate->name) }}
            @else
                ONE (1) {{ strtoupper($plots->first()->plotType->name ?? 'RESIDENTIAL') }} {{ $plots->first()->plot->area ?? '600' }}SQM PLOT
                OF LAND AT {{ strtoupper($purchase->estate->name) }}
            @endif
            SITUATED {{ strtoupper($purchase->estate->location->name ?? '') }},
            {{ strtoupper($purchase->estate->location->city->name ?? '') }}
            {{ strtoupper($purchase->estate->location->city->state->name ?? '') }} STATE.
        </div>

        <div class="prepared-by">
            <div>PREPARED BY:</div>
            <div>{!! nl2br(e($companySettings['contract_prepared_by'])) !!}</div>
        </div>

        <div class="footer">
            <img src="{{ public_path($companySettings['logo_path']) }}" alt="Company Logo" class="small-logo">
        </div>
    </div>

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- Page 2 -->
    <div class="page-container">
        <div class="agreement-date">
            THIS AGREEMENT is made this {{ $agreementDate }}.
        </div>

        <div class="section-header">BETWEEN</div>

        <p>
            <strong>PWAN CHAMPION REALTORS AND ESTATES</strong> is a Limited Liability Company incorporated under the
            Laws of the Federal Republic of Nigeria with its office at 10B Muritala Eletu Street Beside Mayhill
            Hotel Jakande Bus stop, Osapa London, Lekki Pennisula Phase 2, Lagos State (hereinafter referred
            to as <strong>'THE VENDOR'</strong> which expression shall wherever the context so admits include its assigns, legal
            representatives and successors-in-title) of the one part.
        </p>

        <div class="section-header">AND</div>

        <p>
            <strong>{{ strtoupper($client->name) }} OF {{ $client->address ?? ($client->country_of_residence ?? 'Nigeria') }}</strong>. (hereinafter referred to as
            <strong>'THE PURCHASER'</strong> which expression shall wherever the context so admits include his heirs, assigns
            and legal representatives) of the other part.
        </p>

        <div class="section-header">WHEREAS:</div>

        <div class="clause">
            <span class="clause-number">1.</span> The Vendor is a real estate Marketing and Investment Company engaged in the acquisition
            of tracts of land and development of estates which is laid out into schemes and gated
            estates and are thereafter allocated to Subscribers to the Schemes.
        </div>

        <div class="clause">
            <span class="clause-number">2.</span> The Vendor has acquired
            @if($purchase->estate->total_area)
                One (1) vast tract of land measuring {{ number_format($purchase->estate->total_area) }} square meters
            @else
                One (1) vast tract of land measuring 600 square meters
            @endif
            situated at {{ $purchase->estate->location->name ?? '' }},
            {{ $purchase->estate->location->city->name ?? '' }} {{ $purchase->estate->location->city->state->name ?? '' }} STATE
            and described as <strong>{{ strtoupper($purchase->estate->name) }}</strong>.
        </div>

        <div class="clause">
            <span class="clause-number">3.</span> The Vendor with intent to achieve its object of development of Schemes has procured
            <strong>{{ strtoupper($purchase->estate->name) }}</strong> hereinafter referred to as 'the Scheme', whereby interested
            person(s) or organizations subscribes to the Scheme by way of monthly, quarterly
            contribution or outright payment towards ownership of plot(s) of land within the Scheme.
        </div>

        <p>
            <strong>IT IS HEREBY AGREED</strong> that the Purchaser has fully subscribed to
            @if(count($plots) > 1)
                {{ count($plots) }} ({{ App\Services\NumberToWords::convert(count($plots)) }})
                plots of land (measuring {{ number_format($purchase->total_area) }} square meters)
            @else
                One (1) {{ $plots->first()->plotType->name ?? 'Residential' }} {{ $plots->first()->plot->area ?? '600' }}sqm plot
                of land (measuring {{ $plots->first()->plot->area ?? '600' }} square meters)
            @endif
            and the Vendor shall allocate same within the
            Scheme to the Purchaser under the following terms and conditions:
        </p>

        <div class="clause">
            <span class="clause-number">a)</span> The Purchaser has paid the purchase sum of <strong>₦{{ number_format($purchase->total_amount) }} ({{ App\Services\NumberToWords::convert($purchase->total_amount) }} naira)</strong> for the
            full subscription of the said
            @if(count($plots) > 1)
                {{ count($plots) }} {{ App\Services\NumberToWords::convert(count($plots)) }} plots
            @else
                One (1) plot
            @endif
            of land at <strong>{{ strtoupper($purchase->estate->name) }}</strong>, the receipt of which the Vendor hereby acknowledges.
        </div>




        <div class="footer">
            <img src="{{ public_path($companySettings['logo_path']) }}" alt="Company Logo" class="small-logo">
        </div>
    </div>

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- Page 3 -->
    <div class="page-container">

        <div class="clause">
            <span class="clause-number">b)</span> The Vendor shall bear the cost of the preparation of a Survey Plan in the Purchaser's
            name which shall be <strong>{{ strtoupper($client->name) }}</strong>.
        </div>

        <div class="clause">
            <span class="clause-number">c)</span> The Purchaser shall contribute his proportionate share/cost of infrastructure and
            development levies to be communicated to subscribers at the appropriate time.
        </div>
        <div class="clause">
            <span class="clause-number">d)</span> The Purchaser shall endorse and comply with the Rules and Regulations of the Scheme
            to be provided by the Vendor prior to during or after the allocation of the
            @if(count($plots) > 1)
                {{ count($plots) }} plots
            @else
                One (1) {{ $plots->first()->plotType->name ?? 'Residential' }} {{ $plots->first()->plot->area ?? '600' }}sqm plot
            @endif.
        </div>

        <div class="clause">
            <span class="clause-number">e)</span> The Vendor shall allocate the
            @if(count($plots) > 1)
                {{ count($plots) }} {{ App\Services\NumberToWords::convert(count($plots)) }} plots
            @else
                One (1) {{ $plots->first()->plotType->name ?? 'Residential' }} {{ $plots->first()->plot->area ?? '600' }}sqm plot
            @endif
            of land within the Scheme
            as subscribed for by the Purchaser.
        </div>

        <div class="clause">
            <span class="clause-number">f)</span> The Vendor shall from time to time make rules and regulations or issue directives towards
            the realization of the functionality of the Scheme.
        </div>

        <div class="section-header">THE VENDOR COVENANT WITH THE PURCHASER as follows:</div>

        <div class="clause">
            <span class="clause-number">a.</span> To allocate
            @if(count($plots) > 1)
                {{ count($plots) }} ({{ App\Services\NumberToWords::convert(count($plots)) }})
                {{ ucfirst($plots->first()->plotType->name ?? 'Residential') }} {{ $plots->first()->plot->area ?? '600' }}sqm plots
            @else
                One (1) {{ ucfirst($plots->first()->plotType->name ?? 'Residential') }} {{ $plots->first()->plot->area ?? '600' }}sqm plot
            @endif
            of Land to the Purchaser at the time of
            allocation in <strong>{{ strtoupper($purchase->estate->name) }}</strong> situated at {{ $purchase->estate->location->name ?? '' }},
            {{ $purchase->estate->location->city->name ?? '' }} {{ $purchase->estate->location->city->state->name ?? '' }} STATE.
        </div>

        <div class="clause">
            <span class="clause-number">b.</span> To refund to the Purchaser the total money paid less 10% administrative charges and
            30% Agency Fee, if the PURCHASER is no longer interested in the scheme at any time
            before taking possession.
        </div>

        <div class="clause">
            <span class="clause-number">c.</span> The Vendor hereby indemnifies the Purchaser against loss (es) or adverse claim over
            the said
            @if(count($plots) > 1)
                {{ count($plots) }} ({{ App\Services\NumberToWords::convert(count($plots)) }})
                {{ ucfirst($plots->first()->plotType->name ?? 'Residential') }} plots
            @else
                One (1) {{ ucfirst($plots->first()->plotType->name ?? 'Residential') }} {{ $plots->first()->plot->area ?? '600' }}sqm plot
            @endif
            allocated to the Purchaser within the Scheme.
        </div>

        <div class="section-header">THE PURCHASER HEREBY COVENANTS WITH THE VENDOR as follows:</div>

        <div class="clause">
            <span class="clause-number">a.</span> To pay for his development fees in respect of the
            @if(count($plots) > 1)
                {{ count($plots) }} plots
            @else
                One (1) {{ $plots->first()->plotType->name ?? 'Residential' }} {{ $plots->first()->plot->area ?? '600' }}sqm plot
            @endif
            of land.
        </div>

        <div class="clause">
            <span class="clause-number">b.</span> If the PURCHASER wishes to withdraw from this scheme at any time before taking
            possession:
            <div class="subclauses">
                <div>
                    <span class="clause-number">i.</span> To give a notice of 90 days, and 60 days thereafter if the refund is not ready at the
                    expiration of the 1st notice.
                </div>
                <div>
                    <span class="clause-number">ii.</span> An administrative charge of 10% and 30% Agency fee shall be deducted.
                </div>
            </div>
        </div>



        <div class="footer">
            <img src="{{ public_path($companySettings['logo_path']) }}" alt="Company Logo" class="small-logo">
        </div>

    </div>


     <!-- Page Break -->
     <div class="page-break"></div>

     <!-- Page 3 -->
     <div class="page-container">

        <div class="section-header">IT IS HEREBY FURTHER agreed that:</div>

        <div class="clause">
            <span class="clause-number">a.</span> The <strong>PURCHASER</strong> has been briefed and is fully aware of the status of the land and has
            agreed to purchase the land as it is.
        </div>

        <div class="clause">
            <span class="clause-number">b.</span> Both parties covenant to uphold these presents.
        </div>

        <div class="witness-section">
            <p>IN WITNESS WHEREOF, the Parties have hereto set their hand and sealed this day and year first above written.</p>
        </div>




        <div class="signature-block">
            <p>THE COMMON SEAL of <strong>THE VENDOR</strong> is affixed</p>
            <p><strong>PWAN CHAMPION REALTORS AND ESTATES LIMITED</strong></p>

            <p style="margin-top: 20px;">IN THE PRESENCE OF:</p>

            <div style="margin: 30px 0 5px 0;">
                <p><strong>DR. AMB. BENEDICT IBHADON</strong></p>
                <p style="margin-top: 5px;">SECRETARY</p>
            </div>

            <div style="margin: 20px 0 5px 0;">
                <p>DIRECTOR</p>
            </div>
        </div>

        <div class="signature-block" style="margin-top: 40px;">
            <p><strong>SIGNED, SEALED AND DELIVERED</strong></p>
            <p>By the within named '<strong>PURCHASER</strong>'</p>
            <div class="signature-line"></div>

            <p>{{ strtoupper($client->name) }}</p>
            <p>In the presence of:</p>

            <div style="margin-top: 20px;">
                <p>Name: <span class="signature-line" style="width: 80%;"></span></p>
                <p>Address: <span class="signature-line" style="width: 80%;"></span></p>
                <p>Occupation: <span class="signature-line" style="width: 80%;"></span></p>
                <p>Signature: <span class="signature-line" style="width: 80%;"></span></p>
                <p>Date: <span class="signature-line" style="width: 80%;"></span></p>
            </div>
        </div>

        <div class="footer">
            <img src="{{ public_path($companySettings['logo_path']) }}" alt="Company Logo" class="small-logo">
            <p>...land is wealth</p>
        </div>
    </div>
</body>
</html>