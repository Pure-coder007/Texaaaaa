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
        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('pbo_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('pbo_code')->nullable();
            $table->foreignUuid('estate_id')->constrained()->onDelete('restrict');
            $table->integer('total_plots');
            $table->decimal('total_area', 12, 2);
            $table->decimal('base_price', 12, 2);
            $table->decimal('premium_amount', 12, 2)->default(0);
            $table->foreignUuid('promo_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignUuid('promo_code_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('free_plots')->default(0);
            $table->string('payment_plan_type'); // outright, 6_months, 12_months
            $table->decimal('total_amount', 12, 2);
            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->date('purchase_date');
            $table->string('transaction_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
