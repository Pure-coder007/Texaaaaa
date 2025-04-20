@extends('emails.layout')

@section('content')
    <p>Dear {{ $user->name }},</p>

    <p>{{ $body }}</p>

    <div class="alert alert-info">
        <p><strong>Inspection Alert:</strong> A client has scheduled an inspection at one of your properties.</p>
    </div>

    <h3>Inspection Details</h3>
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

    <p>You can view all scheduled inspections at any time through your dashboard.</p>

    <div class="text-center">
        <a href="{{ $pboUrl }}" class="btn">View Inspections</a>
    </div>
@endsection