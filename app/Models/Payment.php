<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'purchase_id',
        'client_id',
        'payment_plan_id',
        'payment_type',
        'amount',
        'transaction_id',
        'payment_method',
        'status',
        'payment_details',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'json',
    ];

    /**
     * Purchase this payment belongs to
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Client who made the payment
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Payment plan this payment belongs to
     */
    public function paymentPlan()
    {
        return $this->belongsTo(PaymentPlan::class);
    }

    /**
     * Payment proof for this payment
     */
    public function paymentProof()
    {
        return $this->hasOne(PaymentProof::class);
    }

}