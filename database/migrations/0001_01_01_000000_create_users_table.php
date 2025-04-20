<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['client', 'pbo', 'admin'])->default('client');
            $table->enum('admin_role', ['estate_manager', 'finance', 'super_admin'])->nullable();
            $table->string('status')->default('active');
            $table->boolean('onboarding_completed')->default(false);


            // Personal Information
            $table->string('spouse_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('nationality')->nullable();
            $table->json('languages_spoken')->nullable();

            // Contact Information
            $table->text('address')->nullable();
            $table->string('country_of_residence')->nullable();
            $table->string('mobile_number')->nullable(); // Already have phone, this is additional

            // Employment Details
            $table->string('occupation')->nullable();
            $table->string('employer_name')->nullable();

            // Next of Kin Details
            $table->string('next_of_kin_name')->nullable();
            $table->string('next_of_kin_relationship')->nullable();
            $table->text('next_of_kin_address')->nullable();
            $table->string('next_of_kin_phone')->nullable();

            // Terms & Submission
            $table->boolean('terms_accepted')->default(false);
            $table->date('submission_date')->nullable();

            // Registration completion tracking
            $table->boolean('registration_completed')->default(false);

            $table->string('pbo_code')->nullable()->unique();
            $table->foreignUuid('referred_by')->nullable();

            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('bank_swift_code')->nullable();
            $table->string('preferred_payment_method')->nullable();
            $table->text('payment_notes')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();


        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
