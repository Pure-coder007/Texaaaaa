<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentPlan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'purchase_id',
        'client_id',
        'total_amount',
        'initial_payment',
        'duration_months',
        'status',
        'premium_percentage',
        'final_due_date',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'initial_payment' => 'decimal:2',
        'duration_months' => 'integer',
        'premium_percentage' => 'decimal:2',
        'final_due_date' => 'date',
    ];

    /**
     * Purchase this payment plan belongs to
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Client this payment plan belongs to
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Payments made under this payment plan
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Calculate the monthly payment amount
     */
    public function monthlyPaymentAmount()
    {
        $remainingAmount = $this->total_amount - $this->initial_payment;
        return $remainingAmount / $this->duration_months;
    }

    /**
     * Calculate the total paid amount
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
}