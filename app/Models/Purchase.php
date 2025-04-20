<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'client_id',
        'pbo_id',
        'pbo_code',
        'estate_id',
        'total_plots',
        'total_area',
        'base_price',
        'premium_amount',
        'promo_id',
        'promo_code_id',
        'free_plots',
        'payment_plan_type',
        'total_amount',
        'status',
        'purchase_date',
        'transaction_id',
        'referral_source'
    ];

    protected $casts = [
        'total_plots' => 'integer',
        'total_area' => 'decimal:2',
        'base_price' => 'decimal:2',
        'premium_amount' => 'decimal:2',
        'free_plots' => 'integer',
        'total_amount' => 'decimal:2',
        'purchase_date' => 'date',
    ];

    /**
     * Client who made the purchase
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * PBO who facilitated the purchase
     */
    public function pbo()
    {
        return $this->belongsTo(User::class, 'pbo_id');
    }

    /**
     * Estate where the purchase was made
     */
    public function estate()
    {
        return $this->belongsTo(Estate::class);
    }

    /**
     * Plots included in this purchase
     */
    public function purchasePlots()
    {
        return $this->hasMany(PurchasePlot::class);
    }

    /**
     * Payment plan for this purchase
     */
    public function paymentPlan()
    {
        return $this->hasOne(PaymentPlan::class);
    }

    /**
     * Payments made for this purchase
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Commission generated from this purchase
     */
    public function pboSale()
    {
        return $this->hasOne(PboSale::class);
    }

    /**
     * Client folder generated for this purchase
     */
    public function clientFolder()
    {
        return $this->hasOne(ClientFolder::class);
    }

    /**
     * Promo applied to this purchase
     */
    public function promo()
    {
        return $this->belongsTo(Promo::class);
    }

    /**
     * Promo code applied to this purchase
     */
    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    /**
     * Calculate the total amount paid so far
     */
    public function totalPaid()
    {
        return $this->payments()->where('status', 'verified')->sum('amount');
    }

    /**
     * Calculate the remaining balance
     */
    public function remainingBalance()
    {
        return $this->total_amount - $this->totalPaid();
    }

    /**
     * Get all documents related to this purchase through the client folder
     */
    public function documents()
    {
        return $this->hasManyThrough(
            ClientDocument::class,
            ClientFolder::class,
            'purchase_id', // Foreign key on ClientFolder
            'client_folder_id', // Foreign key on ClientDocument
            'id', // Local key on Purchase
            'id' // Local key on ClientFolder
        );
    }
}