<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchasePlot extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'purchase_id',
        'plot_id',
        'estate_plot_type_id',
        'is_commercial',
        'is_corner',
        'is_promo_bonus',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'is_commercial' => 'boolean',
        'is_corner' => 'boolean',
        'is_promo_bonus' => 'boolean',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Purchase this plot belongs to
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Plot included in this purchase
     */
    public function plot()
    {
        return $this->belongsTo(Plot::class);
    }

    /**
     * Plot type of this purchase plot
     */
    public function plotType()
    {
        return $this->belongsTo(EstatePlotType::class, 'estate_plot_type_id');
    }
}