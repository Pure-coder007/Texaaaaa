<?php

namespace App\Services;

use App\Mail\Client\InstallmentPaymentReceivedMail;
use App\Mail\Client\OutrightPurchaseCompletedMail;
use App\Models\ClientDocument;
use App\Models\ClientFolder;
use App\Models\Payment;
use App\Models\Purchase;
use App\Settings\SystemSettings;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DocumentGenerationService
{
    protected $systemSettings;

    public function __construct()
    {
        $this->systemSettings = app(SystemSettings::class);
    }

    /**
     * Generate installment receipt document for a specific payment
     */
    public function generateInstallmentReceipt(Purchase $purchase, Payment $payment): ?ClientDocument
    {
        // Get client folder or create if not exists
        $clientFolder = $this->getOrCreateClientFolder($purchase);

        if (!$clientFolder) {
            return null;
        }

        if (!$payment) {
            Log::error('No payment provided for installment receipt: ' . $purchase->id);
            return null;
        }

        // Create a unique name for the installment receipt
        $receiptName = 'Installment Receipt - ' . $payment->transaction_id;

        // Check if this specific payment already has a receipt
        $existingReceipt = $clientFolder->documents()
            ->where('document_type', 'installment_receipt')
            ->where('metadata->payment_id', $payment->id)
            ->first();

        if ($existingReceipt) {
            return $existingReceipt;
        }

        // Create a unique filename for this installment receipt
        $filename = "installment-receipt-{$payment->transaction_id}.pdf";
        $folderPath = "client-documents/{$purchase->client_id}/{$purchase->id}";
        $filePath = "{$folderPath}/receipts/{$filename}";

        // Ensure the directory exists
        Storage::makeDirectory("{$folderPath}/receipts");

        // Generate PDF content specifically for installment receipt
        $html = $this->generateInstallmentReceiptHtml($purchase, $payment);

        try {
            // Generate PDF using Browsershot
            $pdfContent = Browsershot::html($html)
                ->noSandbox()
                ->format('A4')
                ->margins(5, 5, 5, 5)
                ->showBackground()
                ->scale(0.95)
                ->waitUntilNetworkIdle()
                ->pdf();

            // Save the PDF directly to storage
            Storage::put($filePath, $pdfContent);
        } catch (\Exception $e) {
            Log::error('Installment Receipt PDF Generation Error: ' . $e->getMessage());
            throw $e;
        }

        // Create new receipt document
        $document = ClientDocument::create([
            'client_folder_id' => $clientFolder->id,
            'name' => $receiptName,
            'file_path' => $filePath,
            'document_type' => 'installment_receipt',
            'status' => 'completed', // Receipts are always completed
            'is_system_generated' => true,
            'requires_client_signature' => false,
            'requires_admin_signature' => false,
            'version' => '1.0',
            'metadata' => [
                'purchase_id' => $purchase->id,
                'payment_id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'payment_amount' => $payment->amount,
                'payment_date' => $payment->created_at->format('Y-m-d H:i:s'),
                'payment_method' => $payment->payment_method,
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
            'created_by' => $purchase->client_id,
            'updated_by' => $purchase->client_id,
        ]);

        // Attach PDF to document
        if (Storage::exists($filePath)) {
            $document->addMediaFromDisk($filePath, 'local')
                ->toMediaCollection('document_file');

            // Send email notification for subsequent installment payments
                $client = $purchase->client;
                Mail::to($client)->send(new InstallmentPaymentReceivedMail(
                    $client,
                    $payment,
                    $document
                ));

        } else {
            Log::error("File not found for installment receipt document: {$filePath}");
        }

        return $document;
    }

    /**
     * Generate HTML for installment receipt
     */
    private function generateInstallmentReceiptHtml(Purchase $purchase, Payment $payment): string
    {
        // Get company details from settings
        $companySettings = $this->getCompanySettings();

        // Get client data
        $client = $purchase->client;

        // Generate installment-specific receipt number
        $receiptNumber = $this->generateInstallmentReceiptNumber($purchase, $payment);

        // Calculate remaining balance after this payment
        $totalPaidBeforeThisPayment = $purchase->payments()
            ->where('status', 'verified')
            ->where('created_at', '<', $payment->created_at)
            ->sum('amount');

        $totalPaidIncludingThisPayment = $totalPaidBeforeThisPayment + $payment->amount;
        $outstandingBalance = $purchase->total_amount - $totalPaidIncludingThisPayment;

        // Determine payment number/installment number
        $paymentNumber = $purchase->payments()
            ->where('created_at', '<=', $payment->created_at)
            ->count();

        // Generate the view (using special installment receipt template)
        return View::make('documents.installment-receipt', [
            'purchase' => $purchase,
            'client' => $client,
            'payment' => $payment,
            'companySettings' => $companySettings,
            'plots' => $purchase->purchasePlots,
            'date' => now()->format('F j, Y'),
            'receiptNumber' => $receiptNumber,
            'fileNumber' => $this->generateFileNumber($purchase),
            'paymentNumber' => $paymentNumber,
            'totalPaid' => $totalPaidIncludingThisPayment,
            'outstandingBalance' => $outstandingBalance,
            'totalAmount' => $purchase->total_amount,
            'installmentType' => $purchase->payment_plan_type === '6_months' ? '6 MONTHS' : '12 MONTHS'
        ])->render();
    }

    /**
     * Generate receipt number specifically for installment payments
     */
    private function generateInstallmentReceiptNumber(Purchase $purchase, Payment $payment): string
    {
        $year = now()->format('Y');

        // Bank code from payment
        $bankCode = 'XXX';
        if ($payment->payment_method === 'bank_transfer') {
            // Extract bank code from payment details
            $paymentDetails = $payment->payment_details ?? [];
            $bankName = $paymentDetails['bank_name'] ?? '';

            if ($bankName) {
                // Extract first 3 letters of bank name
                $bankCode = strtoupper(substr(str_replace(' ', '', $bankName), 0, 3));
            } else {
                // Default to ZBN (Zenith Bank)
                $bankCode = 'ZBN';
            }
        }

        // Determine estate code
        $estate = $purchase->estate;
        $estateCode = strtoupper(substr(str_replace(' ', '', $estate->name), 0, 3));

        // Get purchase count for sequential numbering
        $purchaseCount = Purchase::whereYear('created_at', $year)->count();
        $purchaseSequential = str_pad($purchaseCount + 1, 4, '0', STR_PAD_LEFT);

        // Count payments made for this purchase to get the installment number
        $paymentCount = $purchase->payments()
            ->where('created_at', '<=', $payment->created_at)
            ->count();

        // Format: YEAR/BANK/ESTATE-CODE/PURCHASE-SEQ/PAYMENT-NUM
        return "{$year}/{$bankCode}/{$estateCode}/{$purchaseSequential}/{$paymentCount}";
    }

    /**
     * Generate receipt document for a purchase
     */
    public function generateReceipt(Purchase $purchase): ?ClientDocument
    {
        // Get client folder or create if not exists
        $clientFolder = $this->getOrCreateClientFolder($purchase);

        if (!$clientFolder) {
            return null;
        }

        // Check if receipt already exists
        $existingReceipt = $clientFolder->documents()
            ->where('document_type', 'receipt')
            ->where('name', 'Receipt - ' . $purchase->transaction_id)
            ->first();

        if ($existingReceipt) {
            return $existingReceipt;
        }

        // Determine document status - automatically completed for receipts
        $documentStatus = 'completed';

        // Filename for the receipt
        $filename = "receipt-{$purchase->transaction_id}.pdf";
        $folderPath = "client-documents/{$purchase->client_id}/{$purchase->id}";
        $filePath = "{$folderPath}/receipts/{$filename}";

        // Ensure the directory exists
        Storage::makeDirectory("{$folderPath}/receipts");

        // Generate PDF content
        $html = $this->generateReceiptHtml($purchase);

        $payment = $purchase->payments()->latest()->first();

        try {
            // Generate PDF using Browsershot
            $pdfContent = Browsershot::html($html)
                ->noSandbox()
                ->format('A4')
                ->margins(5, 5, 5, 5)
                ->showBackground()
                ->scale(0.95)
                ->waitUntilNetworkIdle()
                ->pdf();

            // Save the PDF directly to storage
            Storage::put($filePath, $pdfContent);
        } catch (\Exception $e) {
            Log::error('Receipt PDF Generation Error: ' . $e->getMessage());
            throw $e;
        }

        // Create new receipt document
        $document = ClientDocument::create([
            'client_folder_id' => $clientFolder->id,
            'name' => 'Receipt - ' . $purchase->transaction_id,
            'file_path' => $filePath,
            'document_type' => 'receipt',
            'status' => $documentStatus,
            'is_system_generated' => true,
            'requires_client_signature' => false,
            'requires_admin_signature' => false,
            'version' => '1.0',
            'metadata' => [
                'payment_id' => $payment->id,
                'purchase_id' => $purchase->id,
                'transaction_id' => $purchase->transaction_id,
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
            'created_by' => $purchase->client_id,
            'updated_by' => $purchase->client_id,
        ]);

        // Attach PDF to document
        if (Storage::exists($filePath)) {
            $document->addMediaFromDisk($filePath, 'local')
                ->toMediaCollection('document_file');


        } else {
            Log::error("File not found for document: {$filePath}");
        }

        return $document;
    }

  /**
     * Generate sales agreement document for a purchase
     */
    public function generateSalesAgreement(Purchase $purchase): ?ClientDocument
    {
        // Get client folder or create if not exists
        $clientFolder = $this->getOrCreateClientFolder($purchase);

        if (!$clientFolder) {
            return null;
        }

        // Check if sales agreement already exists
        $existingAgreement = $clientFolder->documents()
            ->where('document_type', 'sales_agreement')
            ->where('name', 'Contract of Sale - ' . $purchase->transaction_id)
            ->first();

        if ($existingAgreement) {
            return $existingAgreement;
        }

        // Filename for the sales agreement
        $filename = "contract-{$purchase->transaction_id}.pdf";
        $folderPath = "client-documents/{$purchase->client_id}/{$purchase->id}";
        $filePath = "{$folderPath}/contracts/{$filename}";

        // Ensure the directory exists
        Storage::makeDirectory("{$folderPath}/contracts");

        // Generate PDF content
        $html = $this->generateSalesAgreementHtml($purchase);

        try {
            // Generate PDF using Browsershot with exact settings to match client example
            $pdfContent = Browsershot::html($html)
                ->noSandbox()
                ->format('A4')
                ->margins(0, 0, 0, 0) // Zero margins for precise control
                ->showBackground()
                ->waitUntilNetworkIdle()
                ->scale(0.98) // Slightly scale the content to match client's spacing
                ->paperSize(8.27, 11.7, 'in') // A4 in inches
                ->pdf();

            // Save the PDF directly to storage
            Storage::put($filePath, $pdfContent);
        } catch (\Exception $e) {
            Log::error('Sales Agreement PDF Generation Error: ' . $e->getMessage());
            throw $e;
        }

        // Create new sales agreement document
        $document = ClientDocument::create([
            'client_folder_id' => $clientFolder->id,
            'name' => 'Contract of Sale - ' . $purchase->transaction_id,
            'file_path' => $filePath,
            'document_type' => 'sales_agreement',
            'status' => 'pending', // Always starts as pending for signatures
            'is_system_generated' => true,
            'requires_client_signature' => true,
            'requires_admin_signature' => true,
            'version' => '1.0',
            'metadata' => [
                'purchase_id' => $purchase->id,
                'transaction_id' => $purchase->transaction_id,
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
            'created_by' => $purchase->client_id,
            'updated_by' => $purchase->client_id,
        ]);

        // Attach PDF to document
        if (Storage::exists($filePath)) {
            $document->addMediaFromDisk($filePath, 'local')
                ->toMediaCollection('document_file');
        } else {
            Log::error("File not found for document: {$filePath}");
        }

        return $document;
    }


    /**
     * Generate allocation letter document for a purchase
     */
    public function generateAllocationLetter(Purchase $purchase): ?ClientDocument
    {
        // Get client folder or create if not exists
        $clientFolder = $this->getOrCreateClientFolder($purchase);

        if (!$clientFolder) {
            return null;
        }

        // Check if allocation letter already exists
        $existingLetter = $clientFolder->documents()
            ->where('document_type', 'allocation_letter')
            ->where('name', 'Allocation Letter - ' . $purchase->transaction_id)
            ->first();

        if ($existingLetter) {
            return $existingLetter;
        }

        // Filename for the allocation letter
        $filename = "allocation-{$purchase->transaction_id}.pdf";
        $folderPath = "client-documents/{$purchase->client_id}/{$purchase->id}";
        $filePath = "{$folderPath}/allocations/{$filename}";

        // Ensure the directory exists
        Storage::makeDirectory("{$folderPath}/allocations");

        // Generate PDF content
        $html = $this->generateAllocationLetterHtml($purchase);

        try {
            // Generate PDF using Browsershot
            $pdfContent = Browsershot::html($html)
                ->noSandbox()
                ->format('A4')
                ->margins(5, 5, 5, 5)
                ->showBackground()
                ->scale(0.95)
                ->waitUntilNetworkIdle()
                ->pdf();

            // Save the PDF directly to storage
            Storage::put($filePath, $pdfContent);
        } catch (\Exception $e) {
            Log::error('Allocation Letter PDF Generation Error: ' . $e->getMessage());
            throw $e;
        }

        // Create new allocation letter document
        $document = ClientDocument::create([
            'client_folder_id' => $clientFolder->id,
            'name' => 'Allocation Letter - ' . $purchase->transaction_id,
            'file_path' => $filePath,
            'document_type' => 'allocation_letter',
            'status' => 'pending', // Requires admin signature
            'is_system_generated' => true,
            'requires_client_signature' => false,
            'requires_admin_signature' => true,
            'version' => '1.0',
            'metadata' => [
                'purchase_id' => $purchase->id,
                'transaction_id' => $purchase->transaction_id,
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
            'created_by' => $purchase->client_id,
            'updated_by' => $purchase->client_id,
        ]);

        // Attach PDF to document
        if (Storage::exists($filePath)) {
            $document->addMediaFromDisk($filePath, 'local')
                ->toMediaCollection('document_file');

            $client = $purchase->client;
            $folder = $document->folder;
            $allDocuments = $folder->documents;


            Mail::to($client)->send(new OutrightPurchaseCompletedMail(
                $client,
                $purchase,
                $allDocuments
            ));

        } else {
            Log::error("File not found for document: {$filePath}");
        }

        return $document;
    }

    /**
     * Get or create a client folder for a purchase
     */
    private function getOrCreateClientFolder(Purchase $purchase): ?ClientFolder
    {
        // Try to find existing folder
        $folder = ClientFolder::where('client_id', $purchase->client_id)
            ->where('purchase_id', $purchase->id)
            ->first();

        if ($folder) {
            return $folder;
        }

        // Create folder path
        $folderPath = "client-documents/{$purchase->client_id}/{$purchase->id}";

        // Ensure the directory exists
        Storage::makeDirectory($folderPath);

        // Create new folder
        try {
            return ClientFolder::create([
                'client_id' => $purchase->client_id,
                'purchase_id' => $purchase->id,
                'name' => $purchase->estate->name . ' - ' . now()->format('Y-m-d'),
                'path' => $folderPath,
                'status' => 'active',
                'folder_type' => 'purchase',
                'metadata' => [
                    'estate_id' => $purchase->estate_id,
                    'estate_name' => $purchase->estate->name,
                    'payment_plan' => $purchase->payment_plan_type,
                    'purchase_date' => $purchase->purchase_date->format('Y-m-d'),
                ],
                'created_by' => $purchase->client_id,
                'updated_by' => $purchase->client_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create client folder: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate HTML for receipt
     */
    private function generateReceiptHtml(Purchase $purchase): string
    {
        // Get company details from settings
        $companySettings = $this->getCompanySettings();

        // Get client data
        $client = $purchase->client;

        // Get payment data
        $payment = $purchase->payments()->latest()->first();

        // Generate the view
        return View::make('documents.receipt', [
            'purchase' => $purchase,
            'client' => $client,
            'payment' => $payment,
            'companySettings' => $companySettings,
            'plots' => $purchase->purchasePlots,
            'date' => now()->format('F j, Y'),
            'receiptNumber' => $this->generateReceiptNumber($purchase),
            'fileNumber' => $this->generateFileNumber($purchase),
        ])->render();
    }

     /**
     * Generate HTML for sales agreement
     */
    private function generateSalesAgreementHtml(Purchase $purchase): string
    {
        // Get company details from settings
        $companySettings = $this->getCompanySettings();

        // Get client data
        $client = $purchase->client;

        // Format the agreement date like "21st day of February, 2025"
        $agreementDate = now()->format('jS \\d\\a\\y \\of F, Y');

        // Generate the view
        return View::make('documents.sales-agreement', [
            'purchase' => $purchase,
            'client' => $client,
            'companySettings' => $companySettings,
            'plots' => $purchase->purchasePlots,
            'date' => now()->format('F j, Y'),
            'agreementDate' => $agreementDate,
        ])->render();
    }

    /**
     * Generate HTML for allocation letter
     */
    private function generateAllocationLetterHtml(Purchase $purchase): string
    {
        // Get company details from settings
        $companySettings = $this->getCompanySettings();

        // Get client data
        $client = $purchase->client;

        // Generate the view
        return View::make('documents.allocation-letter', [
            'purchase' => $purchase,
            'client' => $client,
            'companySettings' => $companySettings,
            'plots' => $purchase->purchasePlots,
            'date' => now()->format('F j, Y'),
        ])->render();
    }

   /**
     * Get company settings
     */
    private function getCompanySettings(): array
    {
        try {
            return $this->systemSettings->getDocumentSettings();
        } catch (\Exception $e) {
            Log::error('Failed to load document settings: ' . $e->getMessage());

            // Fallback to hardcoded values if settings can't be loaded
            return [
                'company_name' => 'PWAN CHAMPION REALTORS AND ESTATE LIMITED',
                'company_address' => 'No 10B Muritala Eletu Street, Beside Mayhill Hotel, Osapa London, Lekki Phase 2, Lagos',
                'company_phone' => '09076126725',
                'company_whatsapp' => '09076126725',
                'company_email' => 'pwanchampion@gmail.com',
                'company_website' => 'www.pwanchampion.com',
                'company_slogan' => '...land is wealth',

                'receipt_title' => 'Sales Receipt',
                'receipt_file_prefix' => 'PWC/ECE/',
                'receipt_signatory_name' => 'AMB. DR. BENEDICT ABUDU IBHADON',
                'receipt_signatory_title' => 'Managing Director',
                'receipt_company_name_short' => 'PWAN CHAMPION',
                'receipt_company_description' => 'REALTORS AND ESTATE LIMITED',

                'allocation_letter_title' => 'PHYSICAL ALLOCATION NOTIFICATION',
                'allocation_note' => 'This letter is temporary pending the receipt of your survey plan.',
                'allocation_footer_text' => 'Subsequently, you are responsible for the clearing of your land after allocation.',

                'contract_title' => 'CONTRACT OF SALE',
                'contract_prepared_by' => "EMMANUEL NDUBISI, ESQ.\nC/O THE LAW FIRM OF OLUKAYODE A. AKOMOLAFE\n2, OLUFUNMILOLA OKIKIOLU STREET,\nOFF TOYIN STREET,\nIKEJA,\nLAGOS.",
                'contract_vendor_description' => 'is a Limited Liability Company incorporated under the Laws of the Federal Republic of Nigeria with its office at 10B Muritala Eletu Street Beside Mayhill Hotel Jakande Bus stop, Osapa London, Lekki Pennisula Phase 2, Lagos State (hereinafter referred to as \'THE VENDOR\' which expression shall wherever the context so admits include its assigns, legal representatives and successors-in-title) of the one part.',

                'document_footer_text' => '...land is wealth',

                'logo_path' => 'logo.png',
            ];
        }
    }
    

    /**
     * Generate receipt number
     */
    private function generateReceiptNumber(Purchase $purchase): string
    {
        $year = now()->format('Y');
        $estate = $purchase->estate;
        $estateCode = strtoupper(substr(str_replace(' ', '', $estate->name), 0, 3));

        // Get bank code from payment
        $payment = $purchase->payments()->latest()->first();
        $bankCode = 'XXX';

        if ($payment && $payment->payment_method === 'bank_transfer') {
            // Extract bank code from payment details
            $paymentDetails = $payment->payment_details ?? [];
            $bankName = $paymentDetails['bank_name'] ?? '';

            if ($bankName) {
                // Extract first 3 letters of bank name
                $bankCode = strtoupper(substr(str_replace(' ', '', $bankName), 0, 3));
            } else {
                // Default to ZNB (Zenith Bank) as in example receipt
                $bankCode = 'ZNB';
            }
        }

        // Generate sequential number
        $count = Purchase::whereYear('created_at', $year)->count();
        $sequential = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        return "{$year}/{$bankCode}/{$sequential}";
    }

    /**
     * Generate file number
     */
    private function generateFileNumber(Purchase $purchase): string
    {
        $prefix = 'PWC';
        $estate = $purchase->estate;
        $estateCode = strtoupper(substr(str_replace(' ', '', $estate->name), 0, 3));
        $year = now()->format('Y');

        // Generate sequential number
        $count = Purchase::whereYear('created_at', $year)->count();
        $sequential = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}/{$estateCode}/{$year}/{$sequential}";
    }
}