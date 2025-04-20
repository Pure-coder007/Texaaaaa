<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('system.site_name', 'Pwan Champion');

        $this->migrator->add('system.company_name', 'PWAN Champion Realtors and Estates Limited');
        $this->migrator->add('system.website', 'www.pwanchampion.com');

        // Payment options
        $this->migrator->add('system.enable_bank_transfer', true);
        $this->migrator->add('system.enable_cash_payment', false);

        // Bank account settings - First bank
        $this->migrator->add('system.bank_name_1', 'First Bank Nigeria');
        $this->migrator->add('system.bank_account_number_1', '1234567890');
        $this->migrator->add('system.bank_account_name_1', 'EstateHub Properties Ltd');

        // Bank account settings - Second bank
        $this->migrator->add('system.bank_name_2', 'Zenith Bank');
        $this->migrator->add('system.bank_account_number_2', '0987654321');
        $this->migrator->add('system.bank_account_name_2', 'EstateHub Properties Ltd');

        // Bank account settings - Third bank
        $this->migrator->add('system.bank_name_3', 'UBA');
        $this->migrator->add('system.bank_account_number_3', '5555555555');
        $this->migrator->add('system.bank_account_name_3', 'EstateHub Properties Ltd');

        // Tax settings
        $this->migrator->add('system.enable_tax', false);
        $this->migrator->add('system.tax_percentage', 0.0);
        $this->migrator->add('system.tax_name', '');
        $this->migrator->add('system.tax_display', false);

        // Platform processing fee settings
        $this->migrator->add('system.enable_processing_fee', false);
        $this->migrator->add('system.processing_fee_type', 'percentage');
        $this->migrator->add('system.processing_fee_value', 0.0);
        $this->migrator->add('system.processing_fee_name', '');
        $this->migrator->add('system.processing_fee_display', false);
        $this->migrator->add('system.min_processing_fee', 0);
        $this->migrator->add('system.max_processing_fee', 0);

        // Site Information - Header
        $this->migrator->add('system.header_phone', '09076126725');
        $this->migrator->add('system.header_email', 'pwanchampion@gmail.com');
        $this->migrator->add('system.show_register_button', true);

         // Bank transfer instructions
         $this->migrator->add('system.bank_transfer_instructions', 'After making the transfer, please upload your payment proof. Include your transaction reference in the transfer narration.');

         // Cash payment settings
         $this->migrator->add('system.cash_payment_instructions', 'Visit our office to make a cash payment. After payment, you\'ll receive a receipt which you can upload through your dashboard.');
         $this->migrator->add('system.cash_payment_office_address', '123 Main Street, City, Country');
         $this->migrator->add('system.cash_payment_office_hours', 'Monday to Friday, 9:00 AM - 5:00 PM');


         $this->migrator->add('system.installment_initial_payment_percentage', 20.0); // Default 20%
         $this->migrator->add('system.installment_default_penalty_percentage', 10.0);


         // Document Settings
        $this->migrator->add('system.document_company_name', 'PWAN CHAMPION REALTORS AND ESTATE LIMITED');
        $this->migrator->add('system.document_company_address', 'No 10B Muritala Eletu Street Beside Mayhill Hotel, Jakande Bus Stop Osapa London Lekki Phase 2 Lagos.');
        $this->migrator->add('system.document_company_phone', '09076126725');
        $this->migrator->add('system.document_company_whatsapp', '09076126725');
        $this->migrator->add('system.document_company_email', 'pwanchampion@gmail.com');
        $this->migrator->add('system.document_company_website', 'www.pwanchampion.com');
        $this->migrator->add('system.document_company_slogan', '...land is wealth');

        // Receipt Settings
        $this->migrator->add('system.receipt_title', 'Sales Receipt');
        $this->migrator->add('system.receipt_file_prefix', 'PWC/ECE/');
        $this->migrator->add('system.receipt_signatory_name', 'AMB. DR. BENEDICT ABUDU IBHADON');
        $this->migrator->add('system.receipt_signatory_title', 'Managing Director');
        $this->migrator->add('system.receipt_company_name_short', 'PWAN CHAMPION');
        $this->migrator->add('system.receipt_company_description', 'REALTORS AND ESTATE LIMITED');

        // Allocation Letter Settings
        $this->migrator->add('system.allocation_letter_title', 'PHYSICAL ALLOCATION NOTIFICATION');
        $this->migrator->add('system.allocation_note', 'This letter is temporary pending the receipt of your survey plan.');
        $this->migrator->add('system.allocation_footer_text', 'Subsequently, you are responsible for the clearing of your land after allocation.');

        // Contract Settings
        $this->migrator->add('system.contract_title', 'CONTRACT OF SALE');
        $this->migrator->add('system.contract_prepared_by', "EMMANUEL NDUBISI, ESQ.\nC/O THE LAW FIRM OF OLUKAYODE A. AKOMOLAFE\n2, OLUFUNMILOLA OKIKIOLU STREET,\nOFF TOYIN STREET,\nIKEJA,\nLAGOS.");
        $this->migrator->add('system.contract_vendor_description', 'is a Limited Liability Company incorporated under the Laws of the Federal Republic of Nigeria with its office at 10B Muritala Eletu Street Beside Mayhill Hotel Jakande Bus stop, Osapa London, Lekki Pennisula Phase 2, Lagos State (hereinafter referred to as \'THE VENDOR\' which expression shall wherever the context so admits include its assigns, legal representatives and successors-in-title) of the one part.');

        $this->migrator->add('system.document_footer_text', '...land is wealth');

    }
};