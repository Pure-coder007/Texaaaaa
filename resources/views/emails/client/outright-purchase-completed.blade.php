@extends('emails.layout')

@section('content')
    <p>Dear {{ $user->name }},</p>

    <p>{{ $body }}</p>

    <div class="alert alert-success">
        <p><strong>Great news!</strong> Your purchase has been completed successfully. Please find your receipt and document(s) attached to this email.</p>
    </div>

    <h3>Purchase Details</h3>
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
            <th>Purchase Date</th>
            <td>{{ $purchaseDate }}</td>
        </tr>
        <tr>
            <th>Amount</th>
            <td>₦{{ number_format($purchaseAmount, 2) }}</td>
        </tr>
    </table>

    <p>The following documents are attached to this email:</p>
    <ul>
        @foreach($documents as $document)
            <li>{{ $document->name }}</li>
        @endforeach
    </ul>

    <p>You can also view and download these documents at any time from your client dashboard.</p>

    <div class="text-center">
        <a href="{{ route('filament.client.pages.dashboard') }}" class="btn">Go to Dashboard</a>
    </div>

    <p class="mt-4">If you have any questions or need assistance, please don't hesitate to contact our customer support team.</p>
@endsection