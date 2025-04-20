<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PboLevel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'direct_sale_commission_percentage',
        'referral_commission_percentage',
        'minimum_sales_count',
        'minimum_sales_value',
        'status',
    ];

    protected $casts = [
        'direct_sale_commission_percentage' => 'decimal:2',
        'referral_commission_percentage' => 'decimal:2',
        'minimum_sales_count' => 'integer',
        'minimum_sales_value' => 'decimal:2',
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class, 'pbo_level_id');
    }

}