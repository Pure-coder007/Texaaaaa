@extends('emails.layout')

@section('content')
    <p>Dear {{ $user->name }},</p>

    <p>{{ $body }}</p>

    <div class="alert alert-success">
        <p><strong>Important:</strong> Below are your login credentials. Please change your password after your first login for security reasons.</p>
    </div>

    <table>
        <tr>
            <th>Email</th>
            <td>{{ $user->email }}</td>
        </tr>
        <tr>
            <th>Password</th>
            <td>{{ $password }}</td>
        </tr>
    </table>

    <p>Use the link below to access the admin panel:</p>

    <div class="text-center">
        <a href="{{ $loginUrl }}" class="btn">Login to Admin Panel</a>
    </div>
@endsection