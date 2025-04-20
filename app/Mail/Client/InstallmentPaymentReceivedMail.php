<?php

namespace App\Mail\Client;

use App\Mail\BaseMail;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class InstallmentPaymentReceivedMail extends BaseMail implements ShouldQueue
{
    /**
     * The payment instance.
     *
     * @var Payment
     */
    protected $payment;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param Payment $payment
     * @param mixed $documents
     * @return void
     */
    public function __construct(User $user, Payment $payment, $documents)
    {
        $purchase = $payment->purchase;

        // Convert to array if it's a collection
        if ($documents instanceof \Illuminate\Database\Eloquent\Collection) {
            $documents = $documents->all();
        }

        $data = [
            'payment' => $payment,
            'purchase' => $purchase,
            'documents' => $documents,
            'estate_name' => $purchase->estate->name,
        ];

        $title = 'Payment Received - ' . $purchase->estate->name;
        $body = 'We have received your installment payment. Thank you for your continued commitment.';

        parent::__construct($user, $title, $body, $data);

        $this->payment = $payment;
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

        $purchase = $this->payment->purchase;
        $totalPaid = $purchase->payments()->where('status', 'verified')->sum('amount');
        $remainingBalance = $purchase->total_amount - $totalPaid;
        $isComplete = $remainingBalance <= 0;

        $mail = $this->view('emails.client.installment-payment-received')
            ->with([
                'user' => $this->user,
                'title' => $this->emailTitle,
                'body' => $this->emailBody,
                'payment' => $this->payment,
                'purchase' => $purchase,
                'estateName' => $this->getProperty('estate_name'),
                'documents' => $this->getProperty('documents'),
                'transactionRef' => $this->payment->transaction_id,
                'paymentAmount' => $this->payment->amount,
                'totalPaid' => $totalPaid,
                'remainingBalance' => $remainingBalance,
                'totalAmount' => $purchase->total_amount,
                'isComplete' => $isComplete,
                'dueDate' => $purchase->paymentPlan ? $purchase->paymentPlan->final_due_date->format('F d, Y') : null,
            ]);

        // Attach documents
        $documents = $this->getProperty('documents');
        if (is_array($documents)) {
            foreach ($documents as $document) {
                if (is_object($document) && method_exists($document, 'getFirstMedia') && $document->getFirstMedia('document_file')) {
                    $mail->attachFromStorageDisk(
                        'public',
                        $document->getFirstMedia('document_file')->id . '/' . $document->getFirstMedia('document_file')->file_name,
                        $document->name . '.pdf'
                    );
                }
            }
        } else if (is_object($documents) && method_exists($documents, 'getFirstMedia') && $documents->getFirstMedia('document_file')) {
            $mail->attachFromStorageDisk(
                'public',
                $documents->getFirstMedia('document_file')->id . '/' . $documents->getFirstMedia('document_file')->file_name,
                $documents->name . '.pdf'
            );
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
        return 'client-installment-payment-received';
    }
}