<?php

namespace App\Observers;

use App\Mail\Admin\InstallmentPaymentNotificationMail;
use App\Mail\Client\ClientPaymentStatusUpdateMail;
use App\Mail\Client\InstallmentPaymentReceivedMail;
use App\Models\ClientDocument;
use App\Models\Payment;
use App\Models\User;
use App\Services\DocumentGenerationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     *
     * @param  \App\Models\Payment  $payment
     * @return void
     */
    public function created(Payment $payment)
    {
        // Only process for installment payments after the first one
        // The first payment is handled by the PurchaseObserver
        $purchase = $payment->purchase;

        if ($purchase->payment_plan_type !== 'outright' &&
            $payment->id !== $purchase->payments()->oldest()->first()->id) {

            try {
                // Generate installment receipt
                $documentService = app(DocumentGenerationService::class);
                $receiptDocument = $documentService->generateInstallmentReceipt($purchase, $payment);

                $documents = $receiptDocument ? [$receiptDocument] : [];

                // Get the client
                $client = $purchase->client;

                // Get admin users for notifications
                $adminUsers = User::where('role', 'admin')
                    ->where('status', 'active')
                    ->where('admin_role', '!=', null)
                    ->get();

                // Find the estate manager if any
                $estateManager = $purchase->estate->manager;

                // // Send payment confirmation to client
                // Mail::to($client)->send(new InstallmentPaymentReceivedMail($client, $payment, $documents));

                // // Notify admins
                // foreach ($adminUsers as $admin) {
                //     Mail::to($admin)->send(new InstallmentPaymentNotificationMail($admin, $payment));
                // }

                // // Notify estate manager if different from general admin
                // if ($estateManager && !$adminUsers->contains($estateManager->id)) {
                //     Mail::to($estateManager)->send(new InstallmentPaymentNotificationMail($estateManager, $payment));
                // }

                // If this payment completes the purchase, generate allocation letter and sales agreement
                $totalPaid = $purchase->payments()->where('status', 'verified')->sum('amount');
                if ($totalPaid >= $purchase->total_amount) {
                    // Generate final documents
                    $documentService->generateSalesAgreement($purchase);
                    $documentService->generateAllocationLetter($purchase);

                    // Update purchase status
                    $purchase->status = 'completed';
                    $purchase->save();


                }
            } catch (\Exception $e) {
                Log::error('Error processing payment notification: ' . $e->getMessage());
            }
        }
    }

    /**
     * Handle the Payment "updated" event.
     *
     * @param  \App\Models\Payment  $payment
     * @return void
     */
    public function updated(Payment $payment)
    {
        // Only notify if status changed
        if ($payment->isDirty('status')) {
            try {
                // Get the client
                $client = $payment->client;
                $purchase = $payment->purchase;

                // Notify client of payment status change
                Mail::to($client)->send(new ClientPaymentStatusUpdateMail($client, $payment));

                // If payment is now verified and it's an installment that completes the purchase
                if ($payment->status === 'verified' && $purchase->payment_plan_type !== 'outright') {
                    $totalPaid = $purchase->payments()->where('status', 'verified')->sum('amount');

                    // If this payment completes the purchase
                    if ($totalPaid >= $purchase->total_amount) {
                        // // Generate final documents if not already done
                        // $documentService = app(DocumentGenerationService::class);

                        // // Check if sales agreement exists
                        // $salesAgreementExists = $purchase->documents()
                        //     ->where('document_type', 'sales_agreement')
                        //     ->exists();

                        // if (!$salesAgreementExists) {
                        //     $documentService->generateSalesAgreement($purchase);
                        // }

                        // // Check if allocation letter exists
                        // $allocationLetterExists = $purchase->documents()
                        //     ->where('document_type', 'allocation_letter')
                        //     ->exists();

                        // if (!$allocationLetterExists) {
                        //     $documentService->generateAllocationLetter($purchase);
                        // }

                        // Update purchase status
                        $purchase->status = 'completed';
                        $purchase->save();

                        // Get all documents to send to client
                        $documents = $purchase->documents()->get();

                        // Send completion email with all documents
                       // Mail::to($client)->send(new OutrightPurchaseCompletedMail($client, $purchase, $documents));


                    }
                }
            } catch (\Exception $e) {
                Log::error('Error processing payment status update notification: ' . $e->getMessage());
            }
        }
    }
}