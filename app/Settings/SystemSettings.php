<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SystemSettings extends Settings
{

    public string $site_name;
    public string $company_name;
    public string $website;


    public bool $enable_bank_transfer;
    public bool $enable_cash_payment;

    // Bank account settings
    public string $bank_name_1;
    public string $bank_account_number_1;
    public string $bank_account_name_1;
    public string $bank_name_2;
    public string $bank_account_number_2;
    public string $bank_account_name_2;
    public string $bank_name_3;
    public string $bank_account_number_3;
    public string $bank_account_name_3;

    // Tax settings
    public bool $enable_tax;
    public float $tax_percentage;
    public string $tax_name;
    public bool $tax_display;

    // Payment instructions
    public string $bank_transfer_instructions;
    public string $cash_payment_instructions;
    public string $cash_payment_office_address;
    public string $cash_payment_office_hours;


    public float $installment_initial_payment_percentage;
    public float $installment_default_penalty_percentage;

    // Platform processing fee settings
    public bool $enable_processing_fee;
    public string $processing_fee_type;
    public float $processing_fee_value;
    public string $processing_fee_name;
    public bool $processing_fee_display;
    public int $min_processing_fee;
    public int $max_processing_fee;



    // Site Information - Header
    public string $header_phone;
    public string $header_email;
    public bool $show_register_button;


    // Company Information For Documents
    public string $document_company_name;
    public string $document_company_address;
    public string $document_company_phone;
    public string $document_company_whatsapp;
    public string $document_company_email;
    public string $document_company_website;
    public string $document_company_slogan;

    // Receipt Settings
    public string $receipt_title;
    public string $receipt_file_prefix;
    public string $receipt_signatory_name;
    public string $receipt_signatory_title;
    public string $receipt_company_name_short;
    public string $receipt_company_description;

    // Allocation Letter Settings
    public string $allocation_letter_title;
    public string $allocation_note;
    public string $allocation_footer_text;

    // Contract Settings
    public string $contract_title;
    public string $contract_prepared_by;
    public string $contract_vendor_description;

    // Document copyright
    public string $document_footer_text;


    public static function group(): string
    {
        return 'system';
    }

    /**
     * Get all available bank accounts as an array
     *
     * @return array
     */
    public function getBankAccounts(): array
    {
        $accounts = [];

        // Only add banks that have data
        if (!empty($this->bank_name_1) && !empty($this->bank_account_number_1)) {
            $accounts[] = [
                'id' => 1,
                'bank_name' => $this->bank_name_1,
                'account_number' => $this->bank_account_number_1,
                'account_name' => $this->bank_account_name_1,
            ];
        }

        if (!empty($this->bank_name_2) && !empty($this->bank_account_number_2)) {
            $accounts[] = [
                'id' => 2,
                'bank_name' => $this->bank_name_2,
                'account_number' => $this->bank_account_number_2,
                'account_name' => $this->bank_account_name_2,
            ];
        }

        if (!empty($this->bank_name_3) && !empty($this->bank_account_number_3)) {
            $accounts[] = [
                'id' => 3,
                'bank_name' => $this->bank_name_3,
                'account_number' => $this->bank_account_number_3,
                'account_name' => $this->bank_account_name_3,
            ];
        }

        return $accounts;
    }


    /**
     * Get a specific bank account by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getBankAccountById(int $id): ?array
    {
        $accounts = $this->getBankAccounts();

        foreach ($accounts as $account) {
            if ($account['id'] === $id) {
                return $account;
            }
        }

        return null;
    }

    // Calculate tax amount based on a given price
    public function calculateTax(float $price): float
    {
        if (!$this->enable_tax) {
            return 0;
        }

        return round($price * ($this->tax_percentage / 100), 2);
    }


    /**
     * Get header information
     *
     * @return array
     */
    public function getHeaderInfo(): array
    {
        return [
            'phone' => $this->header_phone,
            'email' => $this->header_email,
            'show_register_button' => $this->show_register_button,
        ];
    }

    /**
     * Get footer information
     *
     * @return array
     */
    public function getFooterInfo(): array
    {
        return [
            'tagline' => $this->footer_tagline,
            'social' => [
                'facebook' => $this->social_facebook,
                'instagram' => $this->social_instagram,
                'twitter' => $this->social_twitter,
                'linkedin' => $this->social_linkedin,
            ],
            'contact' => [
                'address' => $this->footer_address,
                'phone' => $this->footer_phone,
                'email' => $this->footer_email,
                'hours' => $this->office_hours,
            ],
            'legal' => [
                'company_name' => $this->company_legal_name,
                'terms_url' => $this->terms_url,
                'privacy_url' => $this->privacy_url,
            ],
        ];
    }

    /**
     * Get document settings
     */
    public function getDocumentSettings(): array
    {
        return [
            'company_name' => $this->document_company_name ?? $this->company_name,
            'company_address' => $this->document_company_address ?? $this->cash_payment_office_address,
            'company_phone' => $this->document_company_phone ?? $this->header_phone,
            'company_whatsapp' => $this->document_company_whatsapp ?? $this->header_phone,
            'company_email' => $this->document_company_email ?? $this->header_email,
            'company_website' => $this->document_company_website ?? $this->website,
            'company_slogan' => $this->document_company_slogan ?? '...land is wealth',

            'receipt_title' => $this->receipt_title ?? 'Sales Receipt',
            'receipt_file_prefix' => $this->receipt_file_prefix ?? 'PWC/ECE/',
            'receipt_signatory_name' => $this->receipt_signatory_name ?? 'AMB. DR. BENEDICT ABUDU IBHADON',
            'receipt_signatory_title' => $this->receipt_signatory_title ?? 'Managing Director',
            'receipt_company_name_short' => $this->receipt_company_name_short ?? 'PWAN CHAMPION',
            'receipt_company_description' => $this->receipt_company_description ?? 'REALTORS AND ESTATE LIMITED',

            'allocation_letter_title' => $this->allocation_letter_title ?? 'PHYSICAL ALLOCATION NOTIFICATION',
            'allocation_note' => $this->allocation_note ?? 'This letter is temporary pending the receipt of your survey plan.',
            'allocation_footer_text' => $this->allocation_footer_text ?? 'Subsequently, you are responsible for the clearing of your land after allocation.',

            'contract_title' => $this->contract_title ?? 'CONTRACT OF SALE',
            'contract_prepared_by' => $this->contract_prepared_by ?? "EMMANUEL NDUBISI, ESQ.\nC/O THE LAW FIRM OF OLUKAYODE A. AKOMOLAFE\n2, OLUFUNMILOLA OKIKIOLU STREET,\nOFF TOYIN STREET,\nIKEJA,\nLAGOS.",
            'contract_vendor_description' => $this->contract_vendor_description ?? 'is a Limited Liability Company incorporated under the Laws of the Federal Republic of Nigeria with its office at 10B Muritala Eletu Street Beside Mayhill Hotel Jakande Bus stop, Osapa London, Lekki Pennisula Phase 2, Lagos State (hereinafter referred to as \'THE VENDOR\' which expression shall wherever the context so admits include its assigns, legal representatives and successors-in-title) of the one part.',

            'document_footer_text' => $this->document_footer_text ?? '...land is wealth',

            'logo_path' => 'logo.png',
        ];
    }

}