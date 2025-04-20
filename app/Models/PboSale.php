<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PboSale extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'purchase_id',
        'pbo_id',
        'client_id',
        'sale_type',
        'commission_percentage',
        'commission_amount',
        'status',
        'payment_date',
        'payment_reference',
    ];

    protected $casts = [
        'commission_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // Relationships
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function pbo()
    {
        return $this->belongsTo(User::class, 'pbo_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}