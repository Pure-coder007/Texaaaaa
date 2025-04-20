<?php

namespace App\Mail\Pbo;

use App\Mail\BaseMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class PboWelcomeMail extends BaseMail implements ShouldQueue
{
    /**
     * Create a new message instance.
     *
     * @param  User  $user
     * @param  string  $loginUrl
     * @return void
     */
    public function __construct(User $user, string $loginUrl)
    {
        // Create the data array with the login URL
        $data = [
            'login_url' => $loginUrl
        ];

        // Set default title and body for the welcome email
        $title = 'Welcome to PwanChampion PBO Portal';
        $body = 'Your PBO account has been created. Click the link below to access your portal.';

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

        $mail = $this->view('emails.pbo.welcome')
            ->with([
                'user' => $this->user,
                'title' => $this->emailTitle,
                'body' => $this->emailBody,
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
        return 'agent-welcome';
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