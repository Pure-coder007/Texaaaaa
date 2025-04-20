<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

abstract class BaseMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The user receiving the email.
     *
     * @var User
     */
    protected $user;

    /**
     * The email title.
     *
     * @var string
     */
    protected $emailTitle;

    /**
     * The email body message.
     *
     * @var string
     */
    protected $emailBody;

    /**
     * Additional data for the email.
     *
     * @var array
     */
    protected $data;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string $title
     * @param string $body
     * @param array $data
     */
    public function __construct(User $user, string $title, string $body, array $data = [])
    {
        $this->user = $user;
        $this->emailTitle = $title;
        $this->emailBody = $body;
        $this->data = $data;
    }

    /**
     * Set the common email properties.
     *
     * @return $this
     */
    protected function setCommonProperties()
    {
        // Set from address
        $this->from(config('mail.from.address'), config('mail.from.name'));

        // Set subject
        $this->subject($this->emailTitle);

        // Add tracking ID
        $trackingId = 'notify-' . str_replace(' ', '-', strtolower($this->getIdentifier())) . '-' . time();
        $this->withSymfonyMessage(function ($message) use ($trackingId) {
            $message->getHeaders()->addTextHeader('X-Notification-ID', $trackingId);
        });

        // Set priority if needed
        if ($this->getHighPriority()) {
            $this->priority(1); // High priority
        }

        return $this;
    }

    /**
     * Get a unique identifier for this email type.
     *
     * @return string
     */
    abstract protected function getIdentifier(): string;

    /**
     * Determine if the email should have high priority.
     *
     * @return bool
     */
    protected function getHighPriority()
    {
        return false;
    }

    /**
     * Get property from the data array.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getProperty($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }
}