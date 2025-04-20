@extends('emails.layout')

@section('content')
    <p>Dear {{ $user->name }},</p>

    <p>{{ $body }}</p>

    <div class="alert alert-success">
        <p><strong>Thank you!</strong> We have received your installment payment. Your receipt is attached to this email.</p>
    </div>

    <h3>Payment Details</h3>
    <table>
        <tr>
            <th>Estate</th>
            <td>{{ $estateName }}</td>
        </tr>
        <tr>
            <th>Transaction Reference</th>
            <td>{{ $transactionRef }}</td>
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
        @if($dueDate)
        <tr>
            <th>Due Date</th>
            <td>{{ $dueDate }}</td>
        </tr>
        @endif
    </table>

    @if($isComplete)
    <div class="alert alert-success">
        <p><strong>Congratulations!</strong> Your payment plan is now complete. Your sales agreement and allocation letter will be generated and sent to you shortly.</p>
    </div>
    @else
    <div class="alert alert-info">
        <p><strong>Next Steps:</strong> Continue making payments at your convenience until your balance is fully paid by the due date.</p>
    </div>
    @endif

    <p>Your receipt is attached to this email. You can also access all your documents through your client dashboard at any time.</p>

    <div class="text-center">
        <a href="{{ route('filament.client.pages.dashboard') }}" class="btn">Go to Dashboard</a>
    </div>

    <p class="mt-4">If you have any questions about your payment or need assistance, please don't hesitate to contact our customer support team.</p>
@endsection