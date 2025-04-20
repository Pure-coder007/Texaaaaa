<?php

namespace App\Livewire\Estates;

use App\Models\Estate;
use App\Models\Plot;
use App\Models\EstatePlotType;
use App\Models\Promo;
use App\Models\PromoCode;
use App\Models\User;
use App\Settings\SystemSettings;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class EstateDetailPage extends Component
{
    use WithPagination;

    public Estate $estate;
    public $availablePlots = [];
    public $plotTypes = [];
    public $activePromos = [];
    public $galleryPhotos = [];
    public $activePhoto = null;
    public $plotStats = [];
    public $availablePlotSizes = [];

    // UI state variables
    public $tab = 'plots'; // plots, features, faq, terms, refund
    public $showPurchaseModal = false;

    // Filters
    public $sizeFilter = '';
    public $plotTypeFilter = '';
    public $commercialFilter = false;
    public $cornerFilter = false;
    public $searchQuery = '';
    public $perPage = 10;
    public $page;

    // Selected plots and purchase details
    public $selectedPlots = [];
    public $selectedPlotType = null;
    public $paymentPlanType = 'outright'; // outright, 6_months, 12_months
    public $initialPayment = 0; // Initial payment amount for installment plans

    // Agent referral
    public $agentCode = null;
    public $agentCodeValid = false;
    public $agentCodeMessage = null;
    public $selectedAgentId = null;

    // "How did you hear about us" field
    public $referralSource = '';

    // Promo code for discount
    public $promoCode = null;
    public $promoCodeValid = false;
    public $promoCodeMessage = null;
    public $discountAmount = 0;
    public $discountedPrice = 0;

    // Promo for free plots
    public $promoId = null;
    public $promoValid = false;
    public $promoMessage = null;
    public $selectedPromo = null;
    public $autoAppliedPromo = null;
    public $freePlots = 0;

    // Price calculations
    public $basePrice = 0;
    public $commercialPremium = 0;
    public $cornerPremium = 0;
    public $totalPrice = 0;
    public $totalArea = 0;

    // For tax and processing fee
    public $taxAmount = 0;
    public $processingFee = 0;
    public $totalWithFeesAndTax = 0;

    // System settings
    protected $systemSettings;


    public $selectedQuantities = [];
    public $availableQuantities = [];

    protected $queryString = [
        'tab' => ['except' => 'plots'],
        'plotTypeFilter' => ['except' => ''],
        'sizeFilter' => ['except' => ''],
        'commercialFilter' => ['except' => false],
        'cornerFilter' => ['except' => false],
        'page' => ['except' => 1],
        'searchQuery' => ['except' => '']
    ];

    protected $rules = [
        'paymentPlanType' => 'required|in:outright,6_months,12_months',
        'selectedPlots' => 'required|array|min:1',
        'agentCode' => 'required|string',
        'initialPayment' => 'nullable|numeric',
        'referralSource' => 'required|string'
    ];

    public function boot(SystemSettings $systemSettings)
    {
        $this->systemSettings = $systemSettings;
    }

    public function mount($estate)
    {
        // Load the estate with necessary relationships
        if (is_scalar($estate)) {
            $this->estate = Estate::with([
                'plots' => function($q) {
                    $q->where('status', 'available');
                },
                'plotTypes' => function($q) {
                    $q->where('is_active', true);
                },
                'promos' => function($q) {
                    $q->where('is_active', true)
                      ->where('valid_from', '<=', now())
                      ->where('valid_to', '>=', now());
                },
                'city.state.country',
                'location',
                'manager',
                'media'
            ])->findOrFail($estate);
        } elseif ($estate instanceof Estate) {
            $this->estate = $estate;
            $this->estate->load([
                'plots' => function($q) {
                    $q->where('status', 'available');
                },
                'plotTypes' => function($q) {
                    $q->where('is_active', true);
                },
                'promos' => function($q) {
                    $q->where('is_active', true)
                      ->where('valid_from', '<=', now())
                      ->where('valid_to', '>=', now());
                },
                'city.state.country',
                'location',
                'manager',
                'media'
            ]);
        } else {
            throw new \InvalidArgumentException('Invalid estate parameter provided');
        }

        // Load available plots
        $this->availablePlots = $this->estate->plots;

        // Setup plot types and promos
        $this->plotTypes = $this->estate->plotTypes;
        if ($this->plotTypes->count() > 0) {
            $this->selectedPlotType = $this->plotTypes->first()->id;
        }

        $this->activePromos = $this->estate->promos;

        // Generate plot statistics
        $this->generatePlotStats();

        // Get unique plot sizes for the filter
        $this->availablePlotSizes = $this->availablePlots->pluck('area')->unique()->sort()->values()->all();

        // Get gallery photos
        $this->galleryPhotos = $this->estate->getMedia('estate_images')->all();
        if (count($this->galleryPhotos) > 0) {
            $this->activePhoto = $this->galleryPhotos[0];
        }
    }

    public function generatePlotStats()
    {
        $plots = $this->estate->plots()->get();
        $totalPlots = $plots->count();
        $availablePlots = $plots->where('status', 'available')->count();
        $reservedPlots = $plots->where('status', 'reserved')->count();
        $soldPlots = $plots->where('status', 'sold')->count();

        $minPrice = $plots->min('price');
        $maxPrice = $plots->max('price');
        $minArea = $plots->min('area');
        $maxArea = $plots->max('area');

        $this->plotStats = [
            'total' => $totalPlots,
            'available' => $availablePlots,
            'reserved' => $reservedPlots,
            'sold' => $soldPlots,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'minArea' => $minArea,
            'maxArea' => $maxArea,
            'availability_percentage' => $totalPlots > 0 ? round(($availablePlots / $totalPlots) * 100) : 0
        ];
    }

    public function changeTab($tab)
    {
        $this->tab = $tab;
    }

    public function openPurchaseModal()
    {
        if (!Auth::check()) {
            // Redirect to login page with return URL
            return redirect()->to(route('filament.client.auth.login', ['intended' => url()->current()]));
        }

        if ($this->getSelectedCount() === 0) {
            // Show an error message if no plots are selected
            session()->flash('error', 'Please select at least one plot before proceeding to payment.');
            return;
        }

        // Auto-calculate initial amounts and apply promotions
        $this->calculateTotals();

        // Auto-apply the best promotion if available
        $this->autoApplyBestPromotion();

        // Set default initial payment based on payment plan type
        $this->updateInitialPayment();

        $this->showPurchaseModal = true;
    }

    /**
     * Auto-apply the best promotion available without requiring client intervention
     */
    public function autoApplyBestPromotion()
    {
        $this->autoAppliedPromo = null;
        $this->freePlots = 0;
        $this->promoValid = false;
        $this->promoMessage = null;

        // Only apply promotions for outright payments
        if ($this->paymentPlanType !== 'outright') {
            return;
        }



        if (count($this->selectedPlots) == 0 || empty($this->activePromos)) {
            return;
        }

        // Find the best promo that the customer qualifies for
        $bestPromo = null;
        $maxFreePlots = 0;

        foreach ($this->activePromos as $promo) {
            $purchasedPlots = count($this->selectedPlots);

            if ($purchasedPlots >= $promo->buy_quantity) {
                $sets = floor($purchasedPlots / $promo->buy_quantity);
                $freePlots = $sets * $promo->free_quantity;

                if ($freePlots > $maxFreePlots) {
                    $maxFreePlots = $freePlots;
                    $bestPromo = $promo;
                }
            }
        }



        if ($bestPromo) {
            $this->promoId = $bestPromo->id;
            $this->autoAppliedPromo = $bestPromo;
            $this->freePlots = $maxFreePlots;
            $this->selectedPromo = $bestPromo;
            $this->promoValid = true;
            $this->promoMessage = "Promotion automatically applied! You'll receive {$this->freePlots} free plot(s) with your purchase.";
        }
    }

    public function closePurchaseModal()
    {
        $this->showPurchaseModal = false;
        $this->resetPurchaseModalForm();
    }

    public function resetPurchaseModalForm()
    {
        $this->agentCode = null;
        $this->agentCodeValid = false;
        $this->agentCodeMessage = null;
        $this->selectedAgentId = null;

        $this->referralSource = '';

        $this->promoCode = null;
        $this->promoCodeValid = false;
        $this->promoCodeMessage = null;
        $this->discountAmount = 0;
        $this->discountedPrice = 0;

        $this->promoId = null;
        $this->promoValid = false;
        $this->promoMessage = null;
        $this->selectedPromo = null;
        $this->autoAppliedPromo = null;
        $this->freePlots = 0;

        $this->paymentPlanType = 'outright';
        $this->initialPayment = 0;
        $this->calculateTotals();
    }

    public function resetFilters()
    {
        $this->sizeFilter = '';
        $this->plotTypeFilter = '';
        $this->commercialFilter = false;
        $this->cornerFilter = false;
        $this->searchQuery = '';
        $this->resetPage();
    }

    public function getFilteredPlotsProperty()
    {
        $query = $this->estate->plots()
            ->where('status', 'available');

        // Apply plot type filter
        if ($this->plotTypeFilter) {
            $query->where('estate_plot_type_id', $this->plotTypeFilter);
        }

        // Apply size filter
        if ($this->sizeFilter) {
            $query->where('area', $this->sizeFilter);
        }

        // Apply commercial/corner filter
        if ($this->commercialFilter) {
            $query->where('is_commercial', true);
        }

        if ($this->cornerFilter) {
            $query->where('is_corner', true);
        }


        return $query;
    }

    public function getPaginatedPlotsProperty()
    {
        return $this->filteredPlots->paginate($this->perPage);
    }

    public function updatedSearchQuery()
    {
        $this->resetPage();
    }

    public function updatedPlotTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedSizeFilter()
    {
        $this->resetPage();
    }

    public function updatedCommercialFilter()
    {
        $this->resetPage();
    }

    public function updatedCornerFilter()
    {
        $this->resetPage();
    }

    public function togglePlotSelection($plotId)
    {
        if (in_array($plotId, $this->selectedPlots)) {
            $this->selectedPlots = array_diff($this->selectedPlots, [$plotId]);
        } else {
            $this->selectedPlots[] = $plotId;
        }

        $this->calculateTotals();
        $this->validatePromo();
    }

    public function isPlotSelected($plotId)
    {
        return in_array($plotId, $this->selectedPlots);
    }

    public function calculateTotals()
    {
        $this->basePrice = 0;
        $this->commercialPremium = 0;
        $this->cornerPremium = 0;
        $this->totalPrice = 0;
        $this->totalArea = 0;
        $this->freePlots = 0;

        foreach ($this->selectedQuantities as $key => $quantity) {
            if ($quantity <= 0 || !isset($this->groupedPlots[$key])) {
                continue;
            }

            $group = $this->groupedPlots[$key];

            // Calculate base price based on payment plan
            switch ($this->paymentPlanType) {
                case 'outright':
                    $plotPrice = $group['outright_price'];
                    break;
                case '6_months':
                    $plotPrice = $group['six_month_price'];
                    break;
                case '12_months':
                    $plotPrice = $group['twelve_month_price'];
                    break;
                default:
                    $plotPrice = $group['price'];
            }

            $groupBasePrice = $plotPrice * $quantity;
            $this->basePrice += $groupBasePrice;
            $this->totalArea += $group['size'] * $quantity;

            // Calculate commercial and corner premiums
            if ($group['is_commercial']) {
                $premium = $groupBasePrice * ($this->estate->commercial_plot_premium_percentage / 100);
                $this->commercialPremium += $premium;
            }

            if ($group['is_corner']) {
                $premium = $groupBasePrice * ($this->estate->corner_plot_premium_percentage / 100);
                $this->cornerPremium += $premium;
            }
        }

        // Apply discount if promo code is valid
        if ($this->promoCodeValid) {
            $this->discountedPrice = $this->basePrice + $this->commercialPremium + $this->cornerPremium - $this->discountAmount;
        } else {
            $this->discountedPrice = $this->basePrice + $this->commercialPremium + $this->cornerPremium;
        }

        $this->totalPrice = $this->discountedPrice;

        // Calculate tax and processing fee
        $this->updateTaxAndFees();

        // Recalculate promo benefits
        $this->validatePromo();

        // Update initial payment based on total price (for installment plans)
        $this->updateInitialPayment();
    }

    public function selectPlotsFromCategories()
    {
        $this->selectedPlots = []; // Reset the selected plots array

        foreach ($this->selectedQuantities as $key => $quantity) {
            if ($quantity <= 0 || !isset($this->groupedPlots[$key])) {
                continue;
            }

            $group = $this->groupedPlots[$key];
            $availablePlotIds = $group['plot_ids'];

            // Randomly select the number of plots needed from this category
            $selectedFromCategory = array_slice($availablePlotIds, 0, $quantity);
            $this->selectedPlots = array_merge($this->selectedPlots, $selectedFromCategory);
        }

        return $this->selectedPlots;
    }

    public function updateInitialPayment()
    {
        // Calculate minimum initial payment based on system settings percentage
        $minPercentage = $this->systemSettings->installment_initial_payment_percentage;
        $minInitialPayment = round($this->totalPrice * ($minPercentage / 100));



        if ($this->paymentPlanType === 'outright') {
            // For outright payment, initial payment is the total amount
            $this->initialPayment = $this->totalWithFeesAndTax;
        } else {

            // For installment payment, set initial payment to minimum if not already set
            // or if it's less than the minimum required
            $this->initialPayment = $minInitialPayment;

            // Make sure initial payment doesn't exceed total amount
            if ($this->initialPayment > $this->totalWithFeesAndTax) {
                $this->initialPayment = $this->totalWithFeesAndTax;
            }
        }
    }

    public function updatedPaymentPlanType()
    {
        // Reset promo when switching to installment payment
        if ($this->paymentPlanType !== 'outright') {
            $this->promoId = null;
            $this->autoAppliedPromo = null;
            $this->freePlots = 0;
            $this->promoValid = false;
            $this->promoMessage = null;
            $this->selectedPromo = null;
        }

        $this->calculateTotals();

        // If switching back to outright, try to auto-apply promos again
        if ($this->paymentPlanType === 'outright') {
            $this->autoApplyBestPromotion();
        }

        $this->updateInitialPayment();
    }

    public function updatedInitialPayment()
    {
        // Validate initial payment when it's changed by the user
        $minPercentage = $this->systemSettings->installment_initial_payment_percentage;
        $minInitialPayment = round($this->totalPrice * ($minPercentage / 100));

        if ($this->initialPayment < $minInitialPayment) {
            $this->initialPayment = $minInitialPayment;
        }

        if ($this->initialPayment > $this->totalWithFeesAndTax) {
            $this->initialPayment = $this->totalWithFeesAndTax;
        }
    }

    public function updateTaxAndFees()
    {
        // Calculate tax using system settings
        $this->taxAmount = $this->systemSettings->calculateTax($this->totalPrice);

        // Calculate processing fee based on type (percentage or fixed)
        if ($this->systemSettings->enable_processing_fee) {
            if ($this->systemSettings->processing_fee_type === 'percentage') {
                $this->processingFee = $this->totalPrice * ($this->systemSettings->processing_fee_value / 100);

                // Apply min/max constraints if configured
                if (isset($this->systemSettings->min_processing_fee) && $this->processingFee < $this->systemSettings->min_processing_fee) {
                    $this->processingFee = $this->systemSettings->min_processing_fee;
                }

                if (isset($this->systemSettings->max_processing_fee) && $this->processingFee > $this->systemSettings->max_processing_fee) {
                    $this->processingFee = $this->systemSettings->max_processing_fee;
                }
            } else {
                // Fixed fee
                $this->processingFee = $this->systemSettings->processing_fee_value;
            }
        } else {
            $this->processingFee = 0;
        }

        // Calculate total with tax and fees
        $this->totalWithFeesAndTax = $this->totalPrice + $this->taxAmount + $this->processingFee;
    }

    public function validateAgentCode()
    {
        if (empty($this->agentCode)) {
            $this->resetAgentCode();
            return;
        }

        // Look up agent by code
        $agent = User::where('role', 'pbo')
                    ->where('status', 'active')
                    ->where('pbo_code', $this->agentCode)
                    ->first();

        if (!$agent) {
            $this->resetAgentCode();
            $this->agentCodeMessage = 'Invalid agent code.';
            return;
        }

        // Set the agent ID based on the code
        $this->selectedAgentId = $agent->id;
        $this->agentCodeValid = true;
        $this->agentCodeMessage = "Agent code valid: {$agent->name}";
    }

    public function resetAgentCode()
    {
        $this->agentCodeValid = false;
        $this->agentCodeMessage = null;
        $this->selectedAgentId = null;
    }

    public function validatePromoCode()
    {
        if (empty($this->promoCode)) {
            $this->resetPromoCode();
            return;
        }

        // Find and validate promo code for discount
        $promoCodeObj = PromoCode::where('code', $this->promoCode)
            ->where('status', 'active')
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->where(function($query) {
                $query->whereColumn('usage_limit', '>', 'times_used')
                    ->orWhereNull('usage_limit');
            })
            ->where('estate_id', $this->estate->id)
            ->first();

        if (!$promoCodeObj) {
            $this->resetPromoCode();
            $this->promoCodeMessage = 'Invalid or expired promo code.';
            return;
        }

        // Calculate discount
        if ($promoCodeObj->discount_type === 'percentage') {
            $this->discountAmount = ($this->basePrice + $this->commercialPremium + $this->cornerPremium) * ($promoCodeObj->discount_amount / 100);
        } else { // fixed
            $this->discountAmount = $promoCodeObj->discount_amount;
        }

        // Ensure discount doesn't exceed the total price
        $this->discountAmount = min($this->discountAmount, $this->basePrice + $this->commercialPremium + $this->cornerPremium);

        // Calculate discounted price
        $this->discountedPrice = $this->basePrice + $this->commercialPremium + $this->cornerPremium - $this->discountAmount;
        $this->totalPrice = $this->discountedPrice;

        $this->promoCodeValid = true;

        // Format the message based on discount type
        if ($promoCodeObj->discount_type === 'percentage') {
            $this->promoCodeMessage = "Promo code applied! {$promoCodeObj->discount_amount}% discount.";
        } else {
            $this->promoCodeMessage = "Promo code applied! ₦" . number_format($promoCodeObj->discount_amount) . " discount.";
        }

        // Update tax and fees based on new price
        $this->updateTaxAndFees();

        // Update initial payment based on new total
        $this->updateInitialPayment();
    }

    public function resetPromoCode()
    {
        $this->promoCodeValid = false;
        $this->promoCodeMessage = null;
        $this->discountAmount = 0;
        $this->discountedPrice = $this->basePrice + $this->commercialPremium + $this->cornerPremium;
        $this->totalPrice = $this->discountedPrice;
        $this->updateTaxAndFees();
        $this->updateInitialPayment();
    }

    public function updatedPromoId()
    {
        $this->validatePromo();
    }

    public function validatePromo()
    {
        // Only allow promotions for outright payments
        if ($this->paymentPlanType !== 'outright') {
            $this->freePlots = 0;
            $this->selectedPromo = null;
            $this->promoValid = false;
            $this->promoMessage = "Promotions are only available for outright payments.";
            return;
        }

        // If we have an auto-applied promo, don't override it
        if ($this->autoAppliedPromo && $this->autoAppliedPromo->id == $this->promoId) {
            return;
        }

        $this->freePlots = 0;
        $this->selectedPromo = null;
        $this->promoValid = false;
        $this->promoMessage = null;

        if (!$this->promoId || count($this->selectedPlots) == 0) {
            return;
        }

        // Find the selected promo
        $promo = $this->activePromos->firstWhere('id', $this->promoId);

        if (!$promo) {
            $this->promoMessage = 'Invalid promotion selected.';
            return;
        }

        // Check if enough plots are selected to qualify for the promo
        $purchasedPlots = count($this->selectedPlots);

        if ($purchasedPlots < $promo->buy_quantity) {
            $this->promoMessage = "You need to select at least {$promo->buy_quantity} plots to qualify for this promotion.";
            return;
        }

        // Calculate how many free plots the user is eligible for
        $sets = floor($purchasedPlots / $promo->buy_quantity);
        $this->freePlots = $sets * $promo->free_quantity;

        if ($this->freePlots > 0) {
            $this->selectedPromo = $promo;
            $this->promoValid = true;
            $this->promoMessage = "Promotion applied! You'll receive {$this->freePlots} free plot(s) with your purchase.";
        }
    }

    public function getFreePlotIds()
    {
        if ($this->freePlots <= 0 || $this->paymentPlanType !== 'outright') {
            return [];
        }

        // Get the smallest available plots not already selected by the user
        $availablePlots = Plot::where('estate_id', $this->estate->id)
            ->where('status', 'available')
            ->whereNotIn('id', $this->selectedPlots)
            ->orderBy('area', 'asc') // Order by smallest area first
            ->limit($this->freePlots)
            ->get();


        return $availablePlots->pluck('id')->toArray();
    }

    public function getPlotDetails($plotId)
    {
        $plot = $this->availablePlots->firstWhere('id', $plotId);

        if (!$plot) {
            return null;
        }

        return [
            'area' => $plot->area,
            'price' => $plot->price,
            'is_commercial' => $plot->is_commercial,
            'is_corner' => $plot->is_corner,
        ];
    }

    public function changeActivePhoto($index)
    {
        if (isset($this->galleryPhotos[$index])) {
            $this->activePhoto = $this->galleryPhotos[$index];
        }
    }

    public function getMinimumInitialPaymentAmount()
    {
        $minPercentage = $this->systemSettings->installment_initial_payment_percentage;
        return round($this->totalPrice * ($minPercentage / 100));
    }


    public function proceedToPayment()
{
    $this->validate([
        'paymentPlanType' => 'required|in:outright,6_months,12_months',
        'referralSource' => 'required',
    ]);

    // Check if any plots are selected
    if ($this->getSelectedCount() === 0) {
        $this->addError('plots', 'Please select at least one plot.');
        return;
    }

    // Manual validation for referral source
    if(empty($this->referralSource)) {
        $this->addError('referralSource', 'Please tell us how you heard about us.');
        return;
    }

    if(empty($this->agentCode)) {
        $this->agentCodeValid = false;
        $this->agentCodeMessage = 'Please enter agent code.';
        return;
    }

    $this->validateAgentCode();

    if (!$this->agentCodeValid) {
        return;
    }

    // Convert category selections to actual plot IDs
    $this->selectPlotsFromCategories();

    // Validate initial payment amount for installment plans
    if ($this->paymentPlanType !== 'outright') {
        $minInitialPayment = $this->getMinimumInitialPaymentAmount();

        if ($this->initialPayment < $minInitialPayment) {
            $this->addError('initialPayment', "Initial payment must be at least ₦" . number_format($minInitialPayment) . " (" . $this->systemSettings->installment_initial_payment_percentage . "% of total)");
            return;
        }
    }

    $freePlotIds = [];
    if ($this->paymentPlanType === 'outright' && $this->freePlots > 0) {
        $freePlotIds = $this->getFreePlotIds();
    }

    // Calculate total plots count including both purchased and free plots
    $totalPlotsCount = count($this->selectedPlots);
    if ($this->paymentPlanType === 'outright') {
        $totalPlotsCount += count($freePlotIds);
    }

    // Prepare the purchase data
    $purchaseData = [
        'client_id' => auth()->id(),
        'pbo_id' => $this->selectedAgentId,
        'pbo_code' => $this->agentCode,
        'estate_id' => $this->estate->id,
        'total_plots' => $totalPlotsCount,
        'total_area' => $this->totalArea,
        'base_price' => $this->basePrice,
        'premium_amount' => $this->commercialPremium + $this->cornerPremium,
        'promo_id' => ($this->paymentPlanType === 'outright' && $this->selectedPromo) ? $this->selectedPromo->id : null,
        'promo_code_id' => $this->promoCodeValid ? PromoCode::where('code', $this->promoCode)->first()?->id : null,
        'free_plots' => ($this->paymentPlanType === 'outright') ? $this->freePlots : 0,
        'free_plot_ids' => $freePlotIds,
        'payment_plan_type' => $this->paymentPlanType,
        'total_amount' => $this->totalPrice,
        'tax_amount' => $this->taxAmount,
        'processing_fee' => $this->processingFee,
        'total_with_fees_and_tax' => $this->totalWithFeesAndTax,
        'initial_payment' => $this->initialPayment,
        'selected_plots' => $this->selectedPlots,
        'purchase_date' => now()->format('Y-m-d'),
        'referral_source' => $this->referralSource,
    ];

    // Store the purchase data in the session
    session()->put('pending_purchase', $purchaseData);

    // Redirect to the payment process page
    return redirect()->route('purchases.process');
}


    public function getGroupedPlotsProperty()
{
    $plots = $this->estate->plots()->where('status', 'available')->get();
    $grouped = [];

    foreach ($plots as $plot) {
        $plotType = $plot->plotType;
        if (!$plotType) continue;

        $size = $plot->area;
        $isComer = $plot->is_corner ? 'Corner' : '';
        $isCommercial = $plot->is_commercial ? 'Commercial' : 'Residential';
        $category = "{$isCommercial} {$isComer}";

        $key = "{$category}_{$size}_{$plotType->id}";

        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'category' => $category,
                'size' => $size,
                'plot_type_id' => $plotType->id,
                'plot_type_name' => $plotType->name,
                'count' => 0,
                'price' => $plot->price,
                'outright_price' => $plotType->outright_price,
                'six_month_price' => $plotType->six_month_price,
                'twelve_month_price' => $plotType->twelve_month_price,
                'is_commercial' => $plot->is_commercial,
                'is_corner' => $plot->is_corner,
                'plot_ids' => []
            ];
        }

        $grouped[$key]['count']++;
        $grouped[$key]['plot_ids'][] = $plot->id;
    }

    // Sort by category and size
    uasort($grouped, function($a, $b) {
        if ($a['category'] === $b['category']) {
            return $a['size'] <=> $b['size'];
        }
        return $a['category'] <=> $b['category'];
    });

    return $grouped;
}

public function incrementQuantity($key)
{
    if (!isset($this->selectedQuantities[$key])) {
        $this->selectedQuantities[$key] = 0;
    }

    $maxAvailable = $this->groupedPlots[$key]['count'] ?? 0;

    if ($this->selectedQuantities[$key] < $maxAvailable) {
        $this->selectedQuantities[$key]++;
        $this->calculateTotals();
    }

        // Convert category selections to actual plot IDs
        $this->selectPlotsFromCategories();
}

public function decrementQuantity($key)
{
    if (!isset($this->selectedQuantities[$key])) {
        $this->selectedQuantities[$key] = 0;
    }

    if ($this->selectedQuantities[$key] > 0) {
        $this->selectedQuantities[$key]--;
        $this->calculateTotals();
    }

        // Convert category selections to actual plot IDs
        $this->selectPlotsFromCategories();
}

public function getSelectedCount()
{
    return array_sum($this->selectedQuantities);
}

    public function render()
    {
        return view('livewire.estates.estate-detail-page', [
            'title' => $this->estate->name,
            'paginatedPlots' => $this->paginatedPlots,
            'minInitialPaymentAmount' => $this->getMinimumInitialPaymentAmount(),
            'minInitialPaymentPercentage' => $this->systemSettings->installment_initial_payment_percentage,
            'showTax' => $this->systemSettings->enable_tax && $this->systemSettings->tax_display,
            'taxName' => $this->systemSettings->tax_name,
            'taxPercentage' => $this->systemSettings->tax_percentage,
            'showProcessingFee' => $this->systemSettings->enable_processing_fee && $this->systemSettings->processing_fee_display,
            'processingFeeName' => $this->systemSettings->processing_fee_name,
            'processingFeeType' => $this->systemSettings->processing_fee_type,
        ]);
    }
}
