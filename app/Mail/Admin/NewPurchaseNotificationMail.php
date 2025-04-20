<?php

namespace App\Mail\Admin;

use App\Mail\BaseMail;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewPurchaseNotificationMail extends BaseMail implements ShouldQueue
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
     * @param User $admin
     * @param Purchase $purchase
     * @return void
     */
    public function __construct(User $admin, Purchase $purchase)
    {
        $data = [
            'purchase' => $purchase,
            'client_name' => $purchase->client->name,
            'client_email' => $purchase->client->email,
            'estate_name' => $purchase->estate->name,
            'payment_type' => $purchase->payment_plan_type,
            'amount' => $purchase->total_amount,
            'initial_payment' => $purchase->payments()->first() ? $purchase->payments()->first()->amount : 0,
        ];

        $title = 'New ' . ucfirst($purchase->payment_plan_type) . ' Purchase - ' . $purchase->transaction_id;
        $body = 'A new purchase has been made by ' . $purchase->client->name . ' for ' . $purchase->estate->name;

        parent::__construct($admin, $title, $body, $data);

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

       // $adminUrl = route('filament.admin.resources.purchases.edit', ['record' => $this->purchase->id]);

        $adminUrl = route('filament.admin.resources.transactions.index');

        return $this->view('emails.admin.new-purchase-notification')
            ->with([
                'user' => $this->user,
                'title' => $this->emailTitle,
                'body' => $this->emailBody,
                'purchase' => $this->purchase,
                'clientName' => $this->getProperty('client_name'),
                'clientEmail' => $this->getProperty('client_email'),
                'estateName' => $this->getProperty('estate_name'),
                'paymentType' => $this->getProperty('payment_type'),
                'amount' => $this->getProperty('amount'),
                'initialPayment' => $this->getProperty('initial_payment'),
                'transactionRef' => $this->purchase->transaction_id,
                'purchaseDate' => $this->purchase->purchase_date->format('F d, Y'),
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
        return 'admin-new-purchase-notification';
    }
}