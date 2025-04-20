@extends('emails.layout')

@section('content')
    <p>Dear {{ $user->name }},</p>

    <p>{{ $body }}</p>

    <div class="alert alert-info">
        <p><strong>New {{ ucfirst($paymentType) }} Purchase!</strong> A new purchase has been made and requires your attention.</p>
    </div>

    <h3>Purchase Details</h3>
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
            <th>Purchase Date</th>
            <td>{{ $purchaseDate }}</td>
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
            <th>Total Amount</th>
            <td>₦{{ number_format($amount, 2) }}</td>
        </tr>
        <tr>
            <th>Initial Payment</th>
            <td>₦{{ number_format($initialPayment, 2) }}</td>
        </tr>
    </table>

    <p>Please review this purchase and verify the payment as soon as possible.</p>

    <div class="text-center">
        <a href="{{ $adminUrl }}" class="btn">View Purchase Details</a>
    </div>
@endsection