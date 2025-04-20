<?php

namespace App\Observers;

use App\Mail\Admin\AdminWelcomeMail;
use App\Mail\Pbo\PboWelcomeMail;
use App\Mail\Client\ClientWelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Determine the appropriate welcome email based on the user's role
        switch ($user->role) {
            case 'pbo':
                $this->sendPboWelcomeEmail($user);
                break;
            case 'client':
                $this->sendClientWelcomeEmail($user);
                break;
        }
    }

    /**
     * Send welcome email to agent
     */
    private function sendPboWelcomeEmail(User $user): void
    {

        // Assign a default agent level if none is set
        if (!$user->pbo_level_id) {
            // Get the default entry-level agent level
            $defaultPboLevel = \App\Models\PboLevel::where('status', 'active')
                ->orderBy('minimum_sales_count')  // Order by ascending to get the entry level
                ->orderBy('minimum_sales_value')
                ->first();

            if ($defaultPboLevel) {
                $user->pbo_level_id = $defaultPboLevel->id;
                $user->save();
            }
        }

        // For agents, just send a welcome email without password
        // You need to create an AgentWelcomeMail class similar to AdminWelcomeMail
        $loginUrl = route('filament.pbo.auth.login');

        // Send the agent welcome email (without credentials)
        Mail::to($user->email)->send(new PboWelcomeMail($user, $loginUrl));
    }

    /**
     * Send welcome email to client
     */
    private function sendClientWelcomeEmail(User $user): void
    {
        // For clients, just send a welcome email without password
        // You need to create a ClientWelcomeMail class similar to AdminWelcomeMail
        $loginUrl = route('filament.client.auth.login');

        // Send the client welcome email (without credentials)
        Mail::to($user->email)->send(new ClientWelcomeMail($user, $loginUrl));
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}