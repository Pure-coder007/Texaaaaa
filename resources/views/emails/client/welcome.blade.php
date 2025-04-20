@extends('emails.layout')

@section('content')
    <p>Dear {{ $user->name }},</p>

    <p>{{ $body }}</p>

    <div class="alert alert-info">
        <p><strong>Next Steps:</strong> Click the button below to access your account.</p>
    </div>

    <p>With your PwanChampion account, you can:</p>
    <ul>
        <li>Browse available properties</li>
        <li>Schedule property inspections</li>
        <li>Track your property purchases</li>
        <li>Access important documents</li>
        <li>Manage your payment plans</li>
    </ul>

    <div class="text-center">
        <a href="{{ $loginUrl }}" class="btn">Access My Account</a>
    </div>

    <p class="mt-4">If you have any questions, please contact our customer support team.</p>
@endsection