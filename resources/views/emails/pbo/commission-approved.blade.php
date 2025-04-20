@extends('emails.layout')

@section('content')
    <p>Dear {{ $user->name }},</p>

    <p>{{ $body }}</p>

    <div class="alert alert-success">
        <p><strong>Commission Approved!</strong> Your commission for this sale has been approved and is being processed for payment.</p>
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
            <th>Commission Rate</th>
            <td>{{ $commissionPercentage }}%</td>
        </tr>
        <tr>
            <th>Commission Amount</th>
            <td>₦{{ number_format($commissionAmount, 2) }}</td>
        </tr>
        <tr>
            <th>Approval Date</th>
            <td>{{ $approvedDate }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td><span style="color: #10b981;">Approved</span></td>
        </tr>
    </table>

    <div class="alert alert-info">
        <p><strong>Next Steps:</strong> Our finance team is processing your commission payment. You will receive another notification when the payment has been completed.</p>
    </div>

    <p>You can view your commission details and payment status at any time through your PBO dashboard.</p>

    <div class="text-center">
        <a href="{{ $pboUrl }}" class="btn">View Commission Details</a>
    </div>
@endsection