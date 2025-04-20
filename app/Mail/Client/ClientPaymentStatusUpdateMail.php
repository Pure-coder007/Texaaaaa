<?php

namespace App\Mail\Client;

use App\Mail\BaseMail;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClientPaymentStatusUpdateMail extends BaseMail implements ShouldQueue
{
    protected $payment;

    public function __construct(User $client, Payment $payment)
    {
        $purchase = $payment->purchase;

        $data = [
            'payment' => $payment,
            'purchase' => $purchase,
            'estate_name' => $purchase->estate->name,
            'status' => $payment->status,
            'amount' => $payment->amount,
        ];

        $title = 'Payment Status Update - ' . $payment->transaction_id;
        $body = 'The status of your payment has been updated to ' . ucfirst($payment->status) . '.';

        parent::__construct($client, $title, $body, $data);

        $this->payment = $payment;
    }

    public function build()
    {
        $this->setCommonProperties();

        $purchase = $this->payment->purchase;
        $dashboardUrl = route('filament.client.resources.purchases.index');

        return $this->view('emails.client.payment-status-update')
            ->with([
                'user' => $this->user,
                'title' => $this->emailTitle,
                'body' => $this->emailBody,
                'payment' => $this->payment,
                'purchase' => $purchase,
                'estateName' => $this->getProperty('estate_name'),
                'status' => $this->getProperty('status'),
                'paymentAmount' => $this->getProperty('amount'),
                'transactionRef' => $this->payment->transaction_id,
                'dashboardUrl' => $dashboardUrl,
            ]);
    }

    protected function getIdentifier(): string
    {
        return 'client-payment-status-update';
    }
}