@extends('emails.layout')

@section('content')
    <p>Dear {{ $user->name }},</p>

    <p>{{ $body }}</p>

    <div class="alert alert-success">
        <p><strong>Commission Alert!</strong> You have earned a commission from a new property purchase.</p>
    </div>

    <h3>Commission Details</h3>
    <table>
        <tr>
            <th>Client</th>
            <td>{{ $clientName }}</td>
        </tr>
        <tr>
            <th>Estate</th>
            <td>{{ $estateName }}</td>
        </tr>
        <tr>
            <th>Transaction Reference</th>
            <td>{{ $transactionRef }}</td>
        </tr>
        <tr>
            <th>Purchase Date</th>
            <td>{{ $purchaseDate }}</td>
        </tr>
        <tr>
            <th>Purchase Amount</th>
            <td>₦{{ number_format($purchaseAmount, 2) }}</td>
        </tr>
        <tr>
            <th>Payment Type</th>
            <td>
                @if($paymentType === 'outright')
                    Outright Payment
                @elseif($paymentType === '6_months')
                    6-Month Installment
                @elseif($paymentType === '12_months')
                    12-Month Installment
                @endif
            </td>
        </tr>
        <tr>
            <th>Commission Rate</th>
            <td>{{ $commissionPercentage }}%</td>
        </tr>
        <tr>
            <th>Commission Amount</th>
            <td>₦{{ number_format($commissionAmount, 2) }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                @if($isPending)
                    <span style="color: #f59e0b;">Pending (Will be paid after full payment)</span>
                @else
                    <span style="color: #10b981;">Ready for Processing</span>
                @endif
            </td>
        </tr>
    </table>

    @if($isPending)
    <div class="alert alert-warning">
        <p><strong>Note:</strong> This commission will be processed after the client completes all installment payments.</p>
    </div>
    @else
    <div class="alert alert-success">
        <p><strong>Good news!</strong> This commission is now ready for processing. Our finance team will review and process it accordingly.</p>
    </div>
    @endif

    <p>You can view all your commissions and sales at any time through your PBO dashboard.</p>

    <div class="text-center">
        <a href="{{ $pboUrl }}" class="btn">View Your Sales</a>
    </div>
@endsection