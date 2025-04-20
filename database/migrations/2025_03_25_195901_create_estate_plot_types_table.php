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
        Schema::create('estate_plot_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('estate_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('size_sqm', 10, 2);
            $table->decimal('outright_price', 12, 2);
            $table->decimal('six_month_price', 12, 2);
            $table->decimal('twelve_month_price', 12, 2);
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('estate_plot_types');
    }
};
