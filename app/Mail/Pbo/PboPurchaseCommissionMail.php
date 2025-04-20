<?php

namespace App\Mail\Pbo;

use App\Mail\BaseMail;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class PboPurchaseCommissionMail extends BaseMail implements ShouldQueue
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
     * @param User $pbo
     * @param Purchase $purchase
     * @return void
     */
    public function __construct(User $pbo, Purchase $purchase)
    {
        $pboSale = $purchase->pboSale;

        $data = [
            'purchase' => $purchase,
            'client_name' => $purchase->client->name,
            'estate_name' => $purchase->estate->name,
            'purchase_amount' => $purchase->total_amount,
            'commission_percentage' => $pboSale ? $pboSale->commission_percentage : 0,
            'commission_amount' => $pboSale ? $pboSale->commission_amount : 0,
            'payment_type' => $purchase->payment_plan_type,
        ];

        $title = 'New Commission - ' . $purchase->transaction_id;
        $body = 'You have earned a commission from a new purchase by ' . $purchase->client->name;

        parent::__construct($pbo, $title, $body, $data);

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

        $pboUrl = route('filament.pbo.resources.commission-trackings.index');

        return $this->view('emails.pbo.purchase-commission')
            ->with([
                'user' => $this->user,
                'title' => $this->emailTitle,
                'body' => $this->emailBody,
                'purchase' => $this->purchase,
                'clientName' => $this->getProperty('client_name'),
                'estateName' => $this->getProperty('estate_name'),
                'purchaseAmount' => $this->getProperty('purchase_amount'),
                'commissionPercentage' => $this->getProperty('commission_percentage'),
                'commissionAmount' => $this->getProperty('commission_amount'),
                'paymentType' => $this->getProperty('payment_type'),
                'transactionRef' => $this->purchase->transaction_id,
                'purchaseDate' => $this->purchase->purchase_date->format('F d, Y'),
                'pboUrl' => $pboUrl,
                'isPending' => $this->purchase->payment_plan_type !== 'outright',
            ]);
    }

    /**
     * Get a unique identifier for this email type.
     *
     * @return string
     */
    protected function getIdentifier(): string
    {
        return 'pbo-purchase-commission';
    }
}