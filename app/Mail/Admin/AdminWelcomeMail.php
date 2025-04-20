<?php

namespace App\Mail\Admin;

use App\Mail\BaseMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminWelcomeMail extends BaseMail implements ShouldQueue
{
    /**
     * Create a new message instance.
     *
     * @param  User  $user
     * @param  string  $password
     * @param  string  $loginUrl
     * @return void
     */
    public function __construct(User $user, string $password, string $loginUrl)
    {
        // Create the data array with the password and login URL
        $data = [
            'password' => $password,
            'login_url' => $loginUrl
        ];

        // Set default title and body for the welcome email
        $title = 'Welcome to PwanChampion';
        $body = 'Your account has been created. Use the credentials below to log in.';

        // Call parent constructor with required parameters
        parent::__construct($user, $title, $body, $data);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Set common email properties
        $this->setCommonProperties();

        $mail = $this->view('emails.admin.welcome')
            ->with([
                'user' => $this->user,
                'title' => $this->emailTitle,
                'body' => $this->emailBody,
                'password' => $this->getProperty('password'),
                'loginUrl' => $this->getProperty('login_url'),
            ]);

        return $mail;
    }

    /**
     * Get a unique identifier for this email type.
     *
     * @return string
     */
    protected function getIdentifier(): string
    {
        return 'admin-welcome';
    }

    /**
     * Determine if the email should have high priority.
     *
     * @return bool
     */
    protected function getHighPriority()
    {
        return true;
    }
}