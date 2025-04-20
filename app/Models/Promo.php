<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Promo extends Model implements HasMedia
{
    use HasFactory, HasUuids, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'estate_id',
        'name',
        'description',
        'buy_quantity',
        'free_quantity',
        'valid_from',
        'valid_to',
        'is_active',
    ];

    protected $casts = [
        'buy_quantity' => 'integer',
        'free_quantity' => 'integer',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Estate this promo belongs to
     */
    public function estate()
    {
        return $this->belongsTo(Estate::class);
    }

    /**
     * Purchases using this promo
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Check if promo is valid today
     */
    public function isValidToday()
    {
        $today = now()->startOfDay();
        return $this->is_active &&
               $today->gte($this->valid_from) &&
               $today->lte($this->valid_to);
    }

    /**
     * Register media collections for the model
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('promo_images')
            ->useDisk('public');
    }
}