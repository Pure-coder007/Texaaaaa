<?php

namespace App\Observers;

use App\Mail\Admin\InstallmentPaymentNotificationMail;
use App\Mail\Admin\NewPurchaseNotificationMail;
use App\Mail\Client\InstallmentPaymentReceivedMail;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PurchaseObserver
{
    /**
     * Handle the Purchase "created" event.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return void
     */
    public function created(Purchase $purchase)
    {
        try {
            // Get the client
            $client = $purchase->client;

            // Get all relevant documents for this purchase
            $documents = $purchase->clientFolder ? $purchase->clientFolder->documents : [];

            // Get any admin users for notifications
            $adminUsers = User::where('role', 'admin')
                ->where('status', 'active')
                ->where('admin_role', '!=', null)
                ->get();

            // Find the estate manager if any
            $estateManager = $purchase->estate->manager;

            // Get PBO if available
            $pbo = $purchase->pbo;

            // Handle based on payment plan type
            if ($purchase->payment_plan_type === 'outright') {

                // Notify admins
                foreach ($adminUsers as $admin) {
                    Mail::to($admin)->send(new NewPurchaseNotificationMail($admin, $purchase));
                }

                // Notify estate manager if different from general admin
                if ($estateManager && !$adminUsers->contains($estateManager->id)) {
                    Mail::to($estateManager)->send(new NewPurchaseNotificationMail($estateManager, $purchase));
                }


            } else {
              
                // Notify admins
                foreach ($adminUsers as $admin) {
                    Mail::to($admin)->send(new NewPurchaseNotificationMail($admin, $purchase));
                }

                // Notify estate manager if different from general admin
                if ($estateManager && !$adminUsers->contains($estateManager->id)) {
                    Mail::to($estateManager)->send(new NewPurchaseNotificationMail($estateManager, $purchase));
                }


            }
        } catch (\Exception $e) {
            Log::error('Error sending purchase emails: ' . $e->getMessage());
        }
    }
}