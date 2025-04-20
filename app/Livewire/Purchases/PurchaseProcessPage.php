<?php

namespace App\Livewire\Purchases;

use App\Models\Estate;
use App\Models\Plot;
use App\Models\Purchase;
use App\Models\PurchasePlot;
use App\Models\Payment;
use App\Models\PaymentPlan;
use App\Models\PaymentProof;
use App\Models\User;
use App\Models\PboSale;
use App\Models\Promo;
use App\Models\PromoCode;
use App\Services\DocumentGenerationService;
use App\Settings\SystemSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Rule;

class PurchaseProcessPage extends Component
{
    use WithFileUploads;

    // Purchase data
    public $estate;
    public $selectedPlots = [];
    public $plotDetails = [];
    public $agent;
    public $pboCode;
    public $promoCode;
    public $promoId;
    public $selectedPromo;
    public $paymentPlanType;
    public $basePrice = 0;
    public $premiumAmount = 0;
    public $discountAmount = 0;
    public $totalPrice = 0;
    public $taxAmount = 0;
    public $processingFee = 0;
    public $totalWithFeesAndTax = 0;
    public $initialPayment = 0;
    public $remainingAmount = 0;
    public $totalArea = 0;
    public $freePlots = 0;

    public $freePlotIds = [];

    // For payment processing
    public $transactionReference;
    public $paymentMethod = 'bank_transfer'; // default payment method
    public $selectedBankId;
    public $referralSource = '';
    public $notes; // Additional notes for the payment

    // Purchase tracking
    protected $purchase = null;
    public $purchaseId = null;

    // Payment proof
    #[Rule('required|image|mimes:jpg,jpeg,png,gif|max:5120')] // 5MB max, specifies image types
    public $paymentProofFile;

    // UI control
    public $showSuccessMessage = false;
    public $currentStep = 1; // 1: Details, 2: Payment Selection, 3: Upload Proof

    // Processing state
    public $isProcessing = false;

    // System settings
    protected $systemSettings;

    public function boot(SystemSettings $systemSettings)
    {
        $this->systemSettings = $systemSettings;
    }

    public function mount()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->to(route('filament.client.auth.login', ['intended' => url()->current()]));
        }

        // Load purchase data from session
        $purchaseData = session()->get('pending_purchase');
        if (!$purchaseData) {
            session()->flash('error', 'No purchase data found. Please select plots first.');
            return redirect()->route('home');
        }

        // Load estate
        $this->estate = Estate::findOrFail($purchaseData['estate_id']);

        // Set purchase data from session
        $this->selectedPlots = $purchaseData['selected_plots'];
        $this->referralSource = $purchaseData['referral_source'];
        $this->basePrice = $purchaseData['base_price'];
        $this->premiumAmount = $purchaseData['premium_amount'];
        $this->totalPrice = $purchaseData['total_amount'];
        $this->totalArea = $purchaseData['total_area'];
        $this->paymentPlanType = $purchaseData['payment_plan_type'];
        $this->initialPayment = $purchaseData['initial_payment'];
        $this->taxAmount = $purchaseData['tax_amount'] ?? 0;
        $this->processingFee = $purchaseData['processing_fee'] ?? 0;
        $this->totalWithFeesAndTax = $purchaseData['total_with_fees_and_tax'] ?? $this->totalPrice;
        $this->discountAmount = $purchaseData['discount_amount'] ?? 0;
        $this->pboCode = $purchaseData['pbo_code'] ?? null;
        $this->promoId = $purchaseData['promo_id'] ?? null;
        $this->promoCode = $purchaseData['promo_code_id'] ?? null;
        $this->freePlots = $purchaseData['free_plots'] ?? 0;
        $this->freePlotIds = $purchaseData['free_plot_ids'] ?? [];

        if ($this->systemSettings->enable_bank_transfer) {
            $this->paymentMethod = 'bank_transfer';
        } elseif ($this->systemSettings->enable_cash_payment) {
            $this->paymentMethod = 'cash';
        }

        // Calculate remaining amount if installment plan
        if ($this->paymentPlanType !== 'outright') {
            $this->remainingAmount = $this->totalWithFeesAndTax - $this->initialPayment;
        }

        // Load PBO if code provided
        if ($this->pboCode) {
            $this->agent = User::where('pbo_code', $this->pboCode)
                             ->where('role', 'pbo')
                             ->where('status', 'active')
                             ->first();
        }

        // Generate transaction reference
        $this->transactionReference = 'TX-' . strtoupper(Str::random(10));

        // Load details for each selected plot
        if (!empty($this->selectedPlots)) {
            $plots = Plot::whereIn('id', $this->selectedPlots)->get();

            foreach ($plots as $plot) {
                $plotType = $plot->plotType ? $plot->plotType->name : 'Standard';
                $this->plotDetails[] = [
                    'id' => $plot->id,
                    'area' => $plot->area,
                    'price' => $plot->price,
                    'is_commercial' => $plot->is_commercial,
                    'is_corner' => $plot->is_corner,
                    'type' => $plotType
                ];
            }
        }

        // Load selected promo if promo ID is provided
        if ($this->promoId) {
            $this->selectedPromo = Promo::find($this->promoId);
        }
    }

    public function changePaymentMethod($method)
    {
        // Only allow changing to enabled payment methods
        if ($method === 'bank_transfer' && $this->systemSettings->enable_bank_transfer) {
            $this->paymentMethod = $method;
        } elseif ($method === 'cash' && $this->systemSettings->enable_cash_payment) {
            $this->paymentMethod = $method;
        }
    }
    public function nextStep()
    {
        if ($this->currentStep === 2) {
            // Validate bank selection for bank transfers
            if ($this->paymentMethod === 'bank_transfer' && empty($this->selectedBankId)) {
                session()->flash('error', 'Please select a bank account for the transfer.');
                return;
            }
        }

        $this->currentStep++;
    }

    public function previousStep()
    {
        $this->currentStep--;
    }

    public function submitPaymentProof()
    {
        // Validate payment proof upload
        $this->validate([
            'paymentProofFile' => 'required|image|max:5120', // 5MB max
        ]);

        // Set processing state to true to show loader
        $this->isProcessing = true;

        try {
            DB::beginTransaction();

            // Create purchase record
            $this->purchase = $this->createPurchase();
            $this->purchaseId = $this->purchase->id;

            // Create purchase plots
            $this->createPurchasePlots($this->purchase);

            // Create payment record
            $payment = $this->createPaymentRecord($this->purchase);

            // Create payment proof
            $paymentProof = $this->createPaymentProof($payment);

            // Create payment plan if installment
            if ($this->paymentPlanType !== 'outright') {
                $paymentPlan = $this->createPaymentPlan($this->purchase);
            }

            // Create PBO sale if PBO is associated
            if ($this->agent) {
                $this->createPboSale($this->purchase);
            }

            // Update plot statuses
            $this->updatePlotStatuses();

            // Create client folder and documents using the service
            $this->createClientFolder($this->purchase);

            // Generate all required documents
            $this->generateDocuments();

            DB::commit();

            // Clear the pending purchase data
            session()->forget('pending_purchase');

            // Redirect to the success page
            return redirect()->route('purchases.success', ['purchaseId' => $this->purchaseId]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase processing error: ' . $e->getMessage());
            session()->flash('error', 'Failed to process purchase: ' . $e->getMessage());
            // Reset processing state
            $this->isProcessing = false;
        }
    }

    protected function createPurchase()
    {
        $status = 'pending';

        // If outright payment and amount meets total, mark as completed
        if ($this->paymentPlanType === 'outright' &&
            $this->initialPayment >= $this->totalWithFeesAndTax) {
            $status = 'completed';
        }

        // Calculate total plots (including free plots for outright payments)
        $totalPlots = count($this->selectedPlots);
        if ($this->paymentPlanType === 'outright') {
            $totalPlots += count($this->freePlotIds);
        }

        $purchase = new Purchase();
        $purchase->client_id = auth()->id();
        $purchase->pbo_id = $this->agent ? $this->agent->id : null;
        $purchase->pbo_code = $this->pboCode;
        $purchase->estate_id = $this->estate->id;
        $purchase->total_plots = $totalPlots; // Include free plots in total
        $purchase->total_area = $this->totalArea;
        $purchase->base_price = $this->basePrice;
        $purchase->premium_amount = $this->premiumAmount;
        $purchase->promo_id = ($this->paymentPlanType === 'outright' && $this->selectedPromo)
            ? $this->selectedPromo->id
            : null;
        $purchase->promo_code_id = $this->promoCode ? $this->promoCode : null;
        $purchase->free_plots = ($this->paymentPlanType === 'outright') ? count($this->freePlotIds) : 0;
        $purchase->payment_plan_type = $this->paymentPlanType;
        $purchase->total_amount = $this->totalWithFeesAndTax;
        $purchase->status = $status;
        $purchase->purchase_date = now();
        $purchase->transaction_id = $this->transactionReference;
        $purchase->referral_source = $this->referralSource;
        $purchase->save();

        return $purchase;
    }

    protected function createPurchasePlots($purchase)
    {
        // Create regular purchased plots
        $plots = Plot::whereIn('id', $this->selectedPlots)->get();
        foreach ($plots as $plot) {
            $purchasePlot = new PurchasePlot();
            $purchasePlot->purchase_id = $purchase->id;
            $purchasePlot->plot_id = $plot->id;
            $purchasePlot->estate_plot_type_id = $plot->estate_plot_type_id;
            $purchasePlot->is_commercial = $plot->is_commercial;
            $purchasePlot->is_corner = $plot->is_corner;
            $purchasePlot->is_promo_bonus = false;
            $purchasePlot->unit_price = $plot->price;
            $purchasePlot->total_price = $plot->getCurrentPrice();
            $purchasePlot->save();
        }

        // Create free bonus plots (for outright payments only)
        if ($this->paymentPlanType === 'outright' && !empty($this->freePlotIds)) {
            $freePlots = Plot::whereIn('id', $this->freePlotIds)->get();
            foreach ($freePlots as $plot) {
                $purchasePlot = new PurchasePlot();
                $purchasePlot->purchase_id = $purchase->id;
                $purchasePlot->plot_id = $plot->id;
                $purchasePlot->estate_plot_type_id = $plot->estate_plot_type_id;
                $purchasePlot->is_commercial = $plot->is_commercial;
                $purchasePlot->is_corner = $plot->is_corner;
                $purchasePlot->is_promo_bonus = true; // Mark as promotional bonus
                $purchasePlot->unit_price = 0; // Free plot
                $purchasePlot->total_price = 0; // Free plot
                $purchasePlot->save();
            }
        }
    }

    protected function createPaymentRecord($purchase)
    {
        $paymentStatus = 'verified';

        // For outright payment where initial payment matches total amount
        if ($this->paymentPlanType === 'outright' &&
            $this->initialPayment >= $this->totalWithFeesAndTax) {
            $paymentStatus = 'verified';
        }

        $payment = new Payment();
        $payment->purchase_id = $purchase->id;
        $payment->client_id = auth()->id();
        $payment->payment_plan_id = null; // Will be set later if installment
        $payment->payment_type = $this->paymentPlanType === 'outright' ? 'outright' : 'installment';
        $payment->amount = $this->initialPayment;
        $payment->transaction_id = $this->transactionReference;
        $payment->payment_method = $this->paymentMethod;
        $payment->status = $paymentStatus;

        $payment->payment_details = [
            'tax_amount' => $this->taxAmount,
            'processing_fee' => $this->processingFee,
            'total_with_fees_and_tax' => $this->totalWithFeesAndTax,
            'bank_id' => $this->selectedBankId,
            'discount_amount' => $this->discountAmount,
        ];

        $payment->save();

        return $payment;
    }

    protected function createPaymentProof($payment)
    {
        $paymentProofStatus = 'pending';

        // Match payment status for consistency
        if ($payment->status === 'verified') {
            $paymentProofStatus = 'verified';
        }

        $paymentProof = new PaymentProof();
        $paymentProof->payment_id = $payment->id;
        $paymentProof->transaction_reference = $this->transactionReference;
        $paymentProof->notes = $this->notes;
        $paymentProof->status = $paymentProofStatus;
        $paymentProof->save();

        // Add payment proof file
        if ($this->paymentProofFile) {
            $paymentProof->addMedia($this->paymentProofFile->getRealPath())
                        ->usingName($this->paymentProofFile->getClientOriginalName())
                        ->toMediaCollection('proof_documents');
        }

        return $paymentProof;
    }

    protected function createPaymentPlan($purchase)
    {
        $paymentPlan = new PaymentPlan();
        $paymentPlan->purchase_id = $purchase->id;
        $paymentPlan->client_id = auth()->id();
        $paymentPlan->total_amount = $this->totalWithFeesAndTax;
        $paymentPlan->initial_payment = $this->initialPayment;

        // Set duration based on payment plan type
        if ($this->paymentPlanType === '6_months') {
            $paymentPlan->duration_months = 6;
        } else {
            $paymentPlan->duration_months = 12;
        }

        $paymentPlan->status = 'active';

        // Get premium percentage for the selected plan type
        $premiumPercentage = 0;
        // For the estate's specific plan if available
        $plotType = null;

        // Try to get estate plot type based on selected plots
        if (count($this->selectedPlots) > 0) {
            $firstPlot = Plot::find($this->selectedPlots[0]);
            if ($firstPlot && $firstPlot->estate_plot_type_id) {
                $plotType = $firstPlot->plotType;
            }
        }

        // Calculate premium percentage based on estate plot type pricing
        if ($plotType) {
            if ($this->paymentPlanType === '6_months') {
                $outright = $plotType->outright_price;
                $sixMonth = $plotType->six_month_price;
                $premiumPercentage = ($sixMonth / $outright - 1) * 100;
            } else if ($this->paymentPlanType === '12_months') {
                $outright = $plotType->outright_price;
                $twelveMonth = $plotType->twelve_month_price;
                $premiumPercentage = ($twelveMonth / $outright - 1) * 100;
            }
        } else {
            // Fallback to system settings
            $premiumPercentage = $this->paymentPlanType === '6_months' ? 10 : 20;
        }

        $paymentPlan->premium_percentage = $premiumPercentage;
        $paymentPlan->final_due_date = now()->addMonths($paymentPlan->duration_months);
        $paymentPlan->save();

        // Update the payment record with payment plan ID
        $payment = Payment::where('purchase_id', $purchase->id)->first();
        if ($payment) {
            $payment->payment_plan_id = $paymentPlan->id;
            $payment->save();
        }

        return $paymentPlan;
    }

    protected function createPboSale($purchase)
    {
        // Determine commission percentage
        $commission_percentage = 5.0; // Default commission

        if ($this->agent->pboLevel) {
            $commission_percentage = $this->agent->pboLevel->direct_sale_commission_percentage;
        }

        // Calculate commission amount
        $commission_amount = ($purchase->total_amount * $commission_percentage) / 100;

        // Create PBO sale record - always pending initially
        $pboSale = new PboSale();
        $pboSale->purchase_id = $purchase->id;
        $pboSale->pbo_id = $this->agent->id;
        $pboSale->client_id = auth()->id();
        $pboSale->sale_type = 'direct'; // direct or referral
        $pboSale->commission_percentage = $commission_percentage;
        $pboSale->commission_amount = $commission_amount;
        $pboSale->status = 'pending'; // PBO sales always start as pending until admin review
        $pboSale->save();

        return $pboSale;
    }

    protected function updatePlotStatuses()
    {
        // Set appropriate status based on payment plan
        $status = $this->paymentPlanType === 'outright' ? 'sold' : 'reserved';

        // If outright but payment not covering full amount, keep as reserved
        if ($this->paymentPlanType === 'outright' && $this->initialPayment < $this->totalWithFeesAndTax) {
            $status = 'reserved';
        }

        // Update the status of all selected plots
        Plot::whereIn('id', $this->selectedPlots)
            ->update(['status' => $status]);

        // Also update the status of free plots if any
        if (!empty($this->freePlotIds)) {
            Plot::whereIn('id', $this->freePlotIds)
                ->update(['status' => $status]);
        }
    }

    protected function createClientFolder($purchase)
    {
        // Create a client folder for this purchase
        $clientFolder = new \App\Models\ClientFolder();
        $clientFolder->client_id = auth()->id();
        $clientFolder->purchase_id = $purchase->id;
        $clientFolder->name = 'Purchase - ' . $this->estate->name . ' - ' . now()->format('Y-m-d');
        $clientFolder->path = 'client-documents/' . auth()->id() . '/' . $purchase->id;
        $clientFolder->status = 'active';
        $clientFolder->folder_type = 'purchase';
        $clientFolder->metadata = [
            'estate_name' => $this->estate->name,
            'purchase_date' => now()->format('Y-m-d'),
            'transaction_id' => $this->transactionReference,
            'payment_plan' => $this->paymentPlanType
        ];
        $clientFolder->save();

        return $clientFolder;
    }

    protected function generateDocuments(): void
    {
        if (!$this->purchase) {
            return;
        }

        // Use the DocumentGenerationService to generate documents
        $documentService = app(DocumentGenerationService::class);

        try {
            // Generate receipt for all purchase types
            $documentService->generateReceipt($this->purchase);

            // For outright payments only
            if ($this->purchase->payment_plan_type === 'outright') {
                // Generate sales agreement for outright purchases
                $documentService->generateSalesAgreement($this->purchase);

                // Generate allocation letter only if payment is complete
                $payment = $this->purchase->payments()->latest()->first();
                if ($payment && $payment->status === 'verified') {
                    $documentService->generateAllocationLetter($this->purchase);
                }
            }
            // Installment payments only get receipt initially
        } catch (\Exception $e) {
            // Log error but don't stop the process
            Log::error('Document generation failed: ' . $e->getMessage());
            throw $e; // Re-throw the exception to be caught by the main try-catch block
        }
    }

    public function getBankAccountsProperty()
    {
        return $this->systemSettings->getBankAccounts();
    }

    public function getSelectedBankDetailsProperty()
    {
        if (!$this->selectedBankId) {
            return null;
        }

        return $this->systemSettings->getBankAccountById($this->selectedBankId);
    }

    // Add a computed property to easily check if cash payments are enabled
    public function getIsCashPaymentEnabledProperty()
    {
        return $this->systemSettings->enable_cash_payment;
    }

    // Add a computed property to easily check if bank transfers are enabled
    public function getIsBankTransferEnabledProperty()
    {
        return $this->systemSettings->enable_bank_transfer;
    }

    public function render()
    {
        return view('livewire.purchases.purchase-process-page', [
            'title' => 'Process Purchase | ' . config('app.name'),
            'bankAccounts' => $this->bankAccounts,
            'selectedBankDetails' => $this->selectedBankDetails,
            'showTax' => $this->systemSettings->enable_tax && $this->systemSettings->tax_display,
            'taxName' => $this->systemSettings->tax_name,
            'taxPercentage' => $this->systemSettings->tax_percentage,
            'showProcessingFee' => $this->systemSettings->enable_processing_fee && $this->systemSettings->processing_fee_display,
            'processingFeeName' => $this->systemSettings->processing_fee_name,
            'processingFeeType' => $this->systemSettings->processing_fee_type,
        ]);
    }
}