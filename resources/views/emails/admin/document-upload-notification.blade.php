@extends('emails.layout')

@section('content')
    <p>Dear {{ $user->name }},</p>

    <p>{{ $body }}</p>

    <div class="alert alert-info">
        <p><strong>Document Alert:</strong> A new document has been uploaded to the system.</p>
    </div>

    <h3>Document Details</h3>
    <table>
        <tr>
            <th>Client</th>
            <td>{{ $clientName }} ({{ $clientEmail }})</td>
        </tr>
        <tr>
            <th>Document Name</th>
            <td>{{ $documentName }}</td>
        </tr>
        <tr>
            <th>Document Type</th>
            <td>{{ ucfirst(str_replace('_', ' ', $documentType)) }}</td>
        </tr>
        <tr>
            <th>Upload Date</th>
            <td>{{ $document->created_at->format('F j, Y, g:i a') }}</td>
        </tr>
    </table>

    <p>Please review this document at your earliest convenience.</p>

    <div class="text-center">
        <a href="{{ $adminUrl }}" class="btn">View Document</a>
    </div>
@endsection