<?php

namespace App\Observers;

use App\Mail\Client\ClientInspectionNotificationMail;
use App\Mail\Pbo\PboInspectionNotificationMail;
use App\Models\Inspection;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InspectionObserver
{
    /**
     * Handle the Inspection "created" event.
     *
     * @param  \App\Models\Inspection  $inspection
     * @return void
     */
    public function created(Inspection $inspection)
    {
        try {
            // Notify client
            $client = $inspection->client;
            Mail::to($client)->send(new ClientInspectionNotificationMail($client, $inspection));

            // Notify estate manager if any
            $estateManager = $inspection->estate->manager;
            if ($estateManager) {
                Mail::to($estateManager)->send(new PboInspectionNotificationMail($estateManager, $inspection));
            }

            // If the client was referred by a PBO, notify them too
            if ($client->referrer && $client->referrer->role === 'pbo') {
                Mail::to($client->referrer)->send(new PboInspectionNotificationMail($client->referrer, $inspection));
            }
        } catch (\Exception $e) {
            Log::error('Error sending inspection notification emails: ' . $e->getMessage());
        }
    }

   /**
     * Handle the Inspection "updated" event.
     *
     * @param  \App\Models\Inspection  $inspection
     * @return void
     */
    public function updated(Inspection $inspection)
    {
        // Only notify if status changed
        if ($inspection->isDirty('status')) {
            try {
                // Notify client of status change
                $client = $inspection->client;
                Mail::to($client)->send(new ClientInspectionNotificationMail($client, $inspection));

                // Notify estate manager if any
                $estateManager = $inspection->estate->manager;
                if ($estateManager) {
                    Mail::to($estateManager)->send(new PboInspectionNotificationMail($estateManager, $inspection));
                }

                // If the client was referred by a PBO, notify them too
                if ($client->referrer && $client->referrer->role === 'pbo') {
                    Mail::to($client->referrer)->send(new PboInspectionNotificationMail($client->referrer, $inspection));
                }
            } catch (\Exception $e) {
                Log::error('Error sending inspection status update emails: ' . $e->getMessage());
            }
        }
    }
}