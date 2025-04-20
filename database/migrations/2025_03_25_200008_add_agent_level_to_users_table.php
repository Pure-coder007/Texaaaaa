<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('pbo_level_id')->nullable()->constrained('pbo_levels')->nullOnDelete();
            $table->decimal('custom_direct_commission_percentage', 5, 2)->nullable();
            $table->decimal('custom_referral_commission_percentage', 5, 2)->nullable();
            $table->boolean('use_custom_commission')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['pbo_level_id']);
            $table->dropColumn('pbo_level_id');
            $table->dropColumn('custom_direct_commission_percentage');
            $table->dropColumn('custom_referral_commission_percentage');
            $table->dropColumn('use_custom_commission');
        });
    }
};
