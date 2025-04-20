@extends('emails.layout')

@section('content')
    <p>Dear {{ $user->name }},</p>

    <p>{{ $body }}</p>

    <div class="alert alert-info">
        <p><strong>Inspection Update:</strong> Your property inspection for {{ $estateName }} has been {{ $status }}.</p>
    </div>

    <h3>Inspection Details</h3>
    <table>
        <tr>
            <th>Estate</th>
            <td>{{ $estateName }}</td>
        </tr>
        <tr>
            <th>Date</th>
            <td>{{ $scheduledDate }}</td>
        </tr>
        <tr>
            <th>Time</th>
            <td>{{ $scheduledTime }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                @if($status === 'pending')
                    <span style="color: #f59e0b;">Pending</span>
                @elseif($status === 'completed')
                    <span style="color: #10b981;">Completed</span>
                @elseif($status === 'cancelled')
                    <span style="color: #ef4444;">Cancelled</span>
                @else
                    {{ ucfirst($status) }}
                @endif
            </td>
        </tr>
    </table>

    @if($status === 'pending')
    <div class="alert alert-warning">
        <p><strong>Important:</strong> Please arrive 15 minutes before your scheduled time. If you need to reschedule, please do so at least 24 hours in advance.</p>
    </div>
    @endif

    <p>You can view and manage all your inspections at any time through your client dashboard.</p>

    <div class="text-center">
        <a href="{{ $clientUrl }}" class="btn">View Inspections</a>
    </div>
@endsection