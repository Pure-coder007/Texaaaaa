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
        Schema::create('client_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_folder_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('file_path')->nullable();
            $table->string('document_type'); // receipt, contract, allocation, etc.
            $table->string('status')->default('pending'); // pending, client_signed, admin_signed, completed
            $table->boolean('is_system_generated')->default(false);
            $table->boolean('requires_client_signature')->default(false);
            $table->boolean('requires_admin_signature')->default(false);
            $table->dateTime('client_signed_at')->nullable();
            $table->dateTime('admin_signed_at')->nullable();
            $table->foreignUuid('admin_uploader_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUuid('admin_signer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('version')->default('1.0');
            $table->json('metadata')->nullable();
            $table->foreignUuid('original_document_id')->nullable()->constrained('client_documents')->onDelete('set null');
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
        Schema::dropIfExists('client_documents');
    }
};
