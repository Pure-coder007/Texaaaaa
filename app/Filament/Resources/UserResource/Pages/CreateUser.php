<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Mail\Admin\AdminWelcomeMail;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Add the User model import


class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected static bool $canCreateAnother = false;

    // Generate a random password
    private function generatePassword(): string
    {
        return Str::random(12);
    }

    // Override the handleRecordCreation method to set the role to admin
    protected function handleRecordCreation(array $data): Model
    {
        // Generate a password if not provided or hash the provided one
        $password = $data['password'] ?? $this->generatePassword();

        // If password is plain text, hash it
        if (strlen($password) < 60) {
            $data['password'] = Hash::make($password);
        }

        // Set role to admin
        $data['role'] = 'admin';

        // Create the user
        $user = static::getModel()::create($data);

        // Send welcome email with login credentials
        $this->sendWelcomeEmail($user, $password);

        return $user;
    }

    // Send welcome email to the newly created admin
    protected function sendWelcomeEmail(User $user, string $password): void
    {
        // Get login URL
        $loginUrl = route('filament.admin.auth.login');

        // Send email
        Mail::to($user->email)->send(new AdminWelcomeMail(
            $user,
            $password,
            $loginUrl
        ));

    }

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}