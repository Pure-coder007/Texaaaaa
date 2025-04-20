@extends('emails.layout')

@section('content')
    <p>Dear {{ $user->name }},</p>

    <p>{{ $body }}</p>

    <div class="alert alert-success">
        <p><strong>Payment Completed!</strong> Your commission payment has been processed and should reflect in your account shortly.</p>
    </div>

    <h3>Payment Details</h3>
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
            <th>Payment Date</th>
            <td>{{ $paymentDate }}</td>
        </tr>
        <tr>
            <th>Payment Reference</th>
            <td>{{ $paymentReference }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td><span style="color: #10b981;">Paid</span></td>
        </tr>
    </table>

    <div class="alert alert-info">
        <p><strong>Note:</strong> If you do not receive the payment within 2-3 business days, please contact our finance team for assistance.</p>
    </div>

    <p>You can view your commission details and payment history at any time through your PBO dashboard.</p>

    <div class="text-center">
        <a href="{{ $pboUrl }}" class="btn">View Commission Details</a>
    </div>

    <p class="mt-4">Thank you for your continued partnership with PwanChampion!</p>
@endsection