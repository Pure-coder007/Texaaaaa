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
        Schema::create('estates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignUuid('city_id')->constrained()->onDelete('restrict');
            $table->foreignUuid('location_id')->constrained()->onDelete('restrict');
            $table->text('address')->nullable();
            $table->decimal('total_area', 12, 2)->nullable();
            $table->string('status')->default('active');
            $table->foreignUuid('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('corner_plot_premium_percentage', 5, 2)->default(0);
            $table->decimal('commercial_plot_premium_percentage', 5, 2)->default(0);
            $table->json('faq')->nullable();
            $table->json('terms')->nullable();
            $table->json('refund_policy')->nullable();
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
        Schema::dropIfExists('estates');
    }
};
