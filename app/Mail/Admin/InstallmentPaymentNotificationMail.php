<?php

namespace App\Mail\Admin;

use App\Mail\BaseMail;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class InstallmentPaymentNotificationMail extends BaseMail implements ShouldQueue
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
     * @param User $admin
     * @param Payment $payment
     * @return void
     */
    public function __construct(User $admin, Payment $payment)
    {
        $purchase = $payment->purchase;

        $data = [
            'payment' => $payment,
            'purchase' => $purchase,
            'client_name' => $purchase->client->name,
            'client_email' => $purchase->client->email,
            'estate_name' => $purchase->estate->name,
            'amount' => $payment->amount,
        ];

        $title = 'New Installment Payment - ' . $payment->transaction_id;
        $body = $purchase->client->name . ' has made an installment payment for ' . $purchase->estate->name;

        parent::__construct($admin, $title, $body, $data);

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

       // $adminUrl = route('filament.admin.resources.transactions.index', ['record' => $purchase->id]);

       $adminUrl = route('filament.admin.resources.transactions.index');

        return $this->view('emails.admin.installment-payment-notification')
            ->with([
                'user' => $this->user,
                'title' => $this->emailTitle,
                'body' => $this->emailBody,
                'payment' => $this->payment,
                'purchase' => $purchase,
                'clientName' => $this->getProperty('client_name'),
                'clientEmail' => $this->getProperty('client_email'),
                'estateName' => $this->getProperty('estate_name'),
                'paymentAmount' => $this->getProperty('amount'),
                'totalPaid' => $totalPaid,
                'remainingBalance' => $remainingBalance,
                'totalAmount' => $purchase->total_amount,
                'isComplete' => $isComplete,
                'transactionRef' => $this->payment->transaction_id,
                'paymentDate' => $this->payment->created_at->format('F d, Y'),
                'adminUrl' => $adminUrl,
            ]);
    }

    /**
     * Get a unique identifier for this email type.
     *
     * @return string
     */
    protected function getIdentifier(): string
    {
        return 'admin-installment-payment-notification';
    }
}