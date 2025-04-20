<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientFolder extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'client_id',
        'purchase_id',
        'name',
        'path',
        'status',
        'folder_type',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    /**
     * Client who owns this folder
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Purchase related to this folder
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Documents in this folder
     */
    public function documents()
    {
        return $this->hasMany(ClientDocument::class);
    }
}