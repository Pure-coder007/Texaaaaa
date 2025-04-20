<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromoCode extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'estate_id',
        'code',
        'discount_type',
        'discount_amount',
        'valid_from',
        'valid_until',
        'usage_limit',
        'times_used',
        'status',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'usage_limit' => 'integer',
        'times_used' => 'integer',
    ];

    /**
     * Estate this promo code belongs to
     */
    public function estate()
    {
        return $this->belongsTo(Estate::class);
    }

    /**
     * Purchases using this promo code
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Check if promo code is valid today
     */
    public function isValidToday()
    {
        $today = now()->startOfDay();

        // Check if active, within date range, and usage limit not exceeded
        return $this->status === 'active' &&
               $today->gte($this->valid_from) &&
               $today->lte($this->valid_until) &&
               ($this->usage_limit === null || $this->times_used < $this->usage_limit);
    }

    /**
     * Calculate discount amount for a given price
     */
    public function calculateDiscount($price)
    {
        if ($this->discount_type === 'percentage') {
            return $price * ($this->discount_amount / 100);
        } else { // fixed
            return min($this->discount_amount, $price); // Can't discount more than the price
        }
    }
}