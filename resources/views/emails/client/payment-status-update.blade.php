@extends('emails.layout')

@section('content')
    <p>Dear {{ $user->name }},</p>

    <p>{{ $body }}</p>

    <div class="alert
        @if($status === 'verified')
            alert-success
        @elseif($status === 'pending')
            alert-warning
        @elseif($status === 'failed')
            alert-danger
        @else
            alert-info
        @endif">
        <p><strong>Payment Update:</strong> Your payment of ₦{{ number_format($paymentAmount, 2) }} for {{ $estateName }} is now
        @if($status === 'verified')
            <span style="color: #10b981;">verified</span>
        @elseif($status === 'pending')
            <span style="color: #f59e0b;">pending verification</span>
        @elseif($status === 'failed')
            <span style="color: #ef4444;">failed</span>
        @else
            {{ $status }}
        @endif
        .</p>
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
            <th>Amount</th>
            <td>₦{{ number_format($paymentAmount, 2) }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                @if($status === 'verified')
                    <span style="color: #10b981;">Verified</span>
                @elseif($status === 'pending')
                    <span style="color: #f59e0b;">Pending</span>
                @elseif($status === 'failed')
                    <span style="color: #ef4444;">Failed</span>
                @else
                    {{ ucfirst($status) }}
                @endif
            </td>
        </tr>
    </table>

    @if($status === 'verified')
    <div class="alert alert-success">
        <p><strong>Great news!</strong> Your payment has been verified. Your purchase is now progressing to the next stage.</p>
    </div>
    @elseif($status === 'failed')
    <div class="alert alert-danger">
        <p><strong>Action Required:</strong> Your payment could not be verified. Please contact our support team or try making the payment again.</p>
    </div>
    @endif

    <p>You can view all your payment details at any time through your client dashboard.</p>

    <div class="text-center">
        <a href="{{ $dashboardUrl }}" class="btn">Go to Dashboard</a>
    </div>
@endsection