<?php

namespace App\Observers;

use App\Mail\Pbo\PboCommissionApprovedMail;
use App\Mail\Pbo\PboCommissionPaidMail;
use App\Mail\Pbo\PboPurchaseCommissionMail;
use App\Models\PboSale;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PboSaleObserver
{
    /**
     * Handle the PboSale "created" event.
     *
     * @param  \App\Models\PboSale  $pboSale
     * @return void
     */
    public function created(PboSale $pboSale)
    {
        try {
            // Get the PBO
            $pbo = $pboSale->pbo;

            // Get the purchase
            $purchase = $pboSale->purchase;

            // Only proceed if we have both a PBO and a purchase
            if ($pbo && $purchase) {
                // Send commission notification to PBO
                Mail::to($pbo)->send(new PboPurchaseCommissionMail($pbo, $purchase));
            }
        } catch (\Exception $e) {
            Log::error('Error sending PBO commission notification: ' . $e->getMessage());
        }
    }

    /**
     * Handle the PboSale "updated" event.
     *
     * @param  \App\Models\PboSale  $pboSale
     * @return void
     */
    public function updated(PboSale $pboSale)
    {
        // Check if status was changed
        if ($pboSale->isDirty('status')) {
            try {
                // Get the PBO
                $pbo = $pboSale->pbo;

                // Get the purchase
                $purchase = $pboSale->purchase;

                // Create a status-specific notification for the PBO
                if ($pboSale->status === 'approved') {
                    // Create and send approved commission notification
                     Mail::to($pbo)->send(new PboCommissionApprovedMail($pbo, $pboSale));
                } elseif ($pboSale->status === 'paid') {
                    // Create and send paid commission notification
                   Mail::to($pbo)->send(new PboCommissionPaidMail($pbo, $pboSale));
                }
            } catch (\Exception $e) {
                Log::error('Error sending PBO commission status update notification: ' . $e->getMessage());
            }
        }
    }
}