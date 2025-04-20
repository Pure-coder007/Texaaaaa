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
        Schema::create('purchase_plots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('plot_id')->constrained()->onDelete('restrict');
            $table->foreignUuid('estate_plot_type_id')->constrained()->onDelete('restrict');
            $table->boolean('is_commercial')->default(false);
            $table->boolean('is_corner')->default(false);
            $table->boolean('is_promo_bonus')->default(false);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
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
        Schema::dropIfExists('purchase_plots');
    }
};
