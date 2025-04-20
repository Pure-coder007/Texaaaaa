<?php

namespace App\Mail\Pbo;

use App\Mail\BaseMail;
use App\Models\PboSale;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class PboCommissionPaidMail extends BaseMail implements ShouldQueue
{
    /**
     * The PBO sale instance.
     *
     * @var PboSale
     */
    protected $pboSale;

    /**
     * Create a new message instance.
     *
     * @param User $pbo
     * @param PboSale $pboSale
     * @return void
     */
    public function __construct(User $pbo, PboSale $pboSale)
    {
        $purchase = $pboSale->purchase;

        $data = [
            'pbo_sale' => $pboSale,
            'purchase' => $purchase,
            'client_name' => $purchase->client->name,
            'estate_name' => $purchase->estate->name,
            'purchase_amount' => $purchase->total_amount,
            'commission_percentage' => $pboSale->commission_percentage,
            'commission_amount' => $pboSale->commission_amount,
            'payment_type' => $purchase->payment_plan_type,
            'payment_date' => $pboSale->payment_date ? $pboSale->payment_date->format('F d, Y') : now()->format('F d, Y'),
            'payment_reference' => $pboSale->payment_reference ?? 'N/A',
        ];

        $title = 'Commission Payment Processed - ' . $purchase->transaction_id;
        $body = 'Great news! Your commission payment for the sale to ' . $purchase->client->name . ' has been processed.';

        parent::__construct($pbo, $title, $body, $data);

        $this->pboSale = $pboSale;
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

        $purchase = $this->pboSale->purchase;
        $pboUrl = route('filament.pbo.resources.commission-trackings.index');

        return $this->view('emails.pbo.commission-paid')
            ->with([
                'user' => $this->user,
                'title' => $this->emailTitle,
                'body' => $this->emailBody,
                'pboSale' => $this->pboSale,
                'purchase' => $purchase,
                'clientName' => $this->getProperty('client_name'),
                'estateName' => $this->getProperty('estate_name'),
                'purchaseAmount' => $this->getProperty('purchase_amount'),
                'commissionPercentage' => $this->getProperty('commission_percentage'),
                'commissionAmount' => $this->getProperty('commission_amount'),
                'paymentType' => $this->getProperty('payment_type'),
                'transactionRef' => $purchase->transaction_id,
                'paymentDate' => $this->getProperty('payment_date'),
                'paymentReference' => $this->getProperty('payment_reference'),
                'purchaseDate' => $purchase->purchase_date->format('F d, Y'),
                'pboUrl' => $pboUrl,
            ]);
    }

    /**
     * Get a unique identifier for this email type.
     *
     * @return string
     */
    protected function getIdentifier(): string
    {
        return 'pbo-commission-paid';
    }
}