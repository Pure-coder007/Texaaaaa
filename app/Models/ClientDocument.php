<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ClientDocument extends Model implements HasMedia
{
    use HasFactory, HasUuids, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'client_folder_id',
        'name',
        'file_path',
        'document_type',
        'status',
        'is_system_generated',
        'requires_client_signature',
        'requires_admin_signature',
        'client_signed_at',
        'admin_signed_at',
        'admin_uploader_id',
        'admin_signer_id',
        'version',
        'metadata',
        'original_document_id',
    ];

    protected $casts = [
        'is_system_generated' => 'boolean',
        'requires_client_signature' => 'boolean',
        'requires_admin_signature' => 'boolean',
        'client_signed_at' => 'datetime',
        'admin_signed_at' => 'datetime',
        'metadata' => 'json',
    ];

    /**
     * Folder this document belongs to
     */
    public function folder()
    {
        return $this->belongsTo(ClientFolder::class, 'client_folder_id');
    }

    /**
     * Admin who uploaded this document
     */
    public function adminUploader()
    {
        return $this->belongsTo(User::class, 'admin_uploader_id');
    }

    /**
     * Admin who signed this document
     */
    public function adminSigner()
    {
        return $this->belongsTo(User::class, 'admin_signer_id');
    }

    /**
     * Original document this document is based on
     */
    public function originalDocument()
    {
        return $this->belongsTo(ClientDocument::class, 'original_document_id');
    }

    /**
     * Versions of this document
     */
    public function versions()
    {
        return $this->hasMany(ClientDocument::class, 'original_document_id');
    }

    /**
     * Get the client who owns this document
     */
    public function client()
    {
        return $this->folder->client();
    }

    /**
     * Register media collections for the model
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('document_file')
            ->singleFile();

        $this->addMediaCollection('signed_document')
            ->singleFile();

        $this->addMediaCollection('signatures')
            ->useDisk('public');
    }

    /**
     * Check if the document is signed by both parties
     */
    public function isFullySigned()
    {
        $clientSigned = !$this->requires_client_signature || $this->client_signed_at !== null;
        $adminSigned = !$this->requires_admin_signature || $this->admin_signed_at !== null;

        return $clientSigned && $adminSigned;
    }
}