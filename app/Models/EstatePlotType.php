<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstatePlotType extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'estate_id',
        'name',
        'size_sqm',
        'outright_price',
        'six_month_price',
        'twelve_month_price',
        'is_active',
        'plot_count',
    ];

    protected $casts = [
        'size_sqm' => 'decimal:2',
        'outright_price' => 'decimal:2',
        'six_month_price' => 'decimal:2',
        'twelve_month_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Estate this plot type belongs to
     */
    public function estate()
    {
        return $this->belongsTo(Estate::class);
    }

    /**
     * Plots of this type
     */
    public function plots()
    {
        return $this->hasMany(Plot::class);
    }

    /**
     * Purchase plots of this type
     */
    public function purchasePlots()
    {
        return $this->hasMany(PurchasePlot::class);
    }
}