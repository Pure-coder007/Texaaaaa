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
        Schema::create('plots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('estate_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('estate_plot_type_id')->constrained()->onDelete('restrict');
            $table->decimal('area', 10, 2);
            $table->string('dimensions')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('status')->default('available'); // available, reserved, sold
            $table->boolean('is_commercial')->default(false);
            $table->boolean('is_corner')->default(false);
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
        Schema::dropIfExists('plots');
    }
};
