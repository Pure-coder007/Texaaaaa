@extends('emails.layout')

@section('content')
    <p>Dear {{ $user->name }},</p>

    <p>{{ $body }}</p>

    <div class="alert alert-info">
        <p><strong>Next Steps:</strong> Click the button below to access your PBO portal.</p>
    </div>

    <p>As an PBO with PwanChampion, you'll be able to:</p>
    <ul>
        <li>Refer clients to purchase properties</li>
        <li>Track your commissions and sales</li>
        <li>View available properties and estates</li>
        <li>Earn points through our PBO rewards program</li>
    </ul>

    <div class="text-center">
        <a href="{{ $loginUrl }}" class="btn">Access PBO Portal</a>
    </div>

@endsection