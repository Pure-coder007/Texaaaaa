<?php

namespace App\Mail\Client;

use App\Mail\BaseMail;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class OutrightPurchaseCompletedMail extends BaseMail implements ShouldQueue
{
    /**
     * The purchase instance.
     *
     * @var Purchase
     */
    protected $purchase;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param Purchase $purchase
     * @param array $documents
     * @return void
     */
    public function __construct(User $user, Purchase $purchase, $documents)
    {
        // Convert to array if it's a collection
        if ($documents instanceof \Illuminate\Database\Eloquent\Collection) {
            $documents = $documents->all();
        }

        $data = [
            'purchase' => $purchase,
            'documents' => $documents,
            'estate_name' => $purchase->estate->name,
        ];

        $title = 'Your Purchase is Complete - ' . $purchase->estate->name;
        $body = 'Thank you for your purchase. Your transaction has been completed successfully.';

        parent::__construct($user, $title, $body, $data);

        $this->purchase = $purchase;
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

        $mail = $this->view('emails.client.outright-purchase-completed')
            ->with([
                'user' => $this->user,
                'title' => $this->emailTitle,
                'body' => $this->emailBody,
                'purchase' => $this->purchase,
                'estateName' => $this->getProperty('estate_name'),
                'documents' => $this->getProperty('documents'),
                'transactionRef' => $this->purchase->transaction_id,
                'purchaseAmount' => $this->purchase->total_amount,
                'purchaseDate' => $this->purchase->purchase_date->format('F d, Y'),
            ]);

        // Attach documents
        foreach ($this->getProperty('documents') as $document) {
            if ($document->getFirstMedia('document_file')) {
                $mail->attachFromStorageDisk(
                    'public',
                    $document->getFirstMedia('document_file')->id . '/' . $document->getFirstMedia('document_file')->file_name,
                    $document->name . '.pdf'
                );
            }
        }

        return $mail;
    }

    /**
     * Get a unique identifier for this email type.
     *
     * @return string
     */
    protected function getIdentifier(): string
    {
        return 'client-outright-purchase-completed';
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