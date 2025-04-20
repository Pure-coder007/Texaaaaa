@extends('emails.layout')

@section('content')
    <p>Dear {{ $user->name }},</p>

    <p>{{ $body }}</p>

    <div class="alert alert-info">
        <p><strong>New Installment Payment!</strong> A client has made a new installment payment.</p>
    </div>

    <h3>Payment Details</h3>
    <table>
        <tr>
            <th>Client</th>
            <td>{{ $clientName }} ({{ $clientEmail }})</td>
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
            <th>Payment Date</th>
            <td>{{ $paymentDate }}</td>
        </tr>
        <tr>
            <th>Payment Amount</th>
            <td>₦{{ number_format($paymentAmount, 2) }}</td>
        </tr>
        <tr>
            <th>Total Paid To Date</th>
            <td>₦{{ number_format($totalPaid, 2) }}</td>
        </tr>
        <tr>
            <th>Total Amount</th>
            <td>₦{{ number_format($totalAmount, 2) }}</td>
        </tr>
        <tr>
            <th>Remaining Balance</th>
            <td>₦{{ number_format($remainingBalance, 2) }}</td>
        </tr>
    </table>

    @if($isComplete)
    <div class="alert alert-success">
        <p><strong>Payment Completed!</strong> This payment completes the client's installment plan. The sales agreement and allocation letter should be generated and sent to the client.</p>
    </div>
    @endif

    <p>Please verify this payment as soon as possible.</p>

    <div class="text-center">
        <a href="{{ $adminUrl }}" class="btn">View Purchase Details</a>
    </div>
@endsection