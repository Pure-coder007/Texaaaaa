<?php

namespace App\Livewire\Purchases;

use App\Models\ClientFolder;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PurchaseSuccessPage extends Component
{
    public $purchaseId;
    public $purchase;
    public $documents = [];
    public $transactionReference;
    public $paymentPlanType;
    public $initialPayment = 0;
    public $paymentMethod;
    public $totalWithFeesAndTax;
    public $isFullyPaid = false;
    public $purchaseAmount = 0; // Added this property

    public function mount($purchaseId)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->to(route('filament.client.auth.login'));
        }

        $this->purchaseId = $purchaseId;

        // Load the purchase
        $this->purchase = Purchase::where('id', $this->purchaseId)
            ->where('client_id', auth()->id())
            ->first();

        if (!$this->purchase) {
            session()->flash('error', 'Purchase information not found.');
            return redirect()->route('filament.client.pages.dashboard');
        }

        // Set properties needed for display
        $this->transactionReference = $this->purchase->transaction_id;
        $this->paymentPlanType = $this->purchase->payment_plan_type;

        $payment = $this->purchase->payments()->latest()->first();
        if ($payment) {
            $this->initialPayment = $payment->amount;
            $this->purchaseAmount = $payment->amount; // Set purchase amount
            $this->paymentMethod = $payment->payment_method;
        }

        $this->totalWithFeesAndTax = $this->purchase->total_amount;

        // Load documents
        $this->loadDocuments();

        // Determine if fully paid
        if ($this->paymentPlanType === 'outright') {
            $this->isFullyPaid = $payment && $payment->status === 'verified';
        }
    }

    protected function loadDocuments()
    {
        // Find client folder for this purchase
        $clientFolder = ClientFolder::where('purchase_id', $this->purchaseId)
            ->where('client_id', auth()->id())
            ->first();

        if ($clientFolder) {
            // Get all documents for display
            $this->documents = $clientFolder->documents()->get();
        }
    }

    public function render()
    {
        return view('livewire.purchases.purchase-success', [
            'title' => 'Purchase Complete | ' . config('app.name'),
            'clientId' => auth()->id(),
            'purchaseDate' => $this->purchase ? $this->purchase->purchase_date->format('F d, Y') : now()->format('F d, Y'),
            'isOutrightPayment' => $this->paymentPlanType === 'outright',
            'documents' => $this->documents,
            'transactionReference' => $this->transactionReference,
            'paymentMethod' => $this->paymentMethod ?? 'bank_transfer',
            'paymentPlanType' => $this->paymentPlanType,
            'purchaseAmount' => $this->purchaseAmount,
            'isFullyPaid' => $this->isFullyPaid
        ]);
    }
}