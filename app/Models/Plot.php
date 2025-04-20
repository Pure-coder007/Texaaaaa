<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Plot extends Model implements HasMedia
{
    use HasFactory, HasUuids, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'estate_id',
        'estate_plot_type_id',
        'area',
        'dimensions',
        'price',
        'status',
        'is_commercial',
        'is_corner',
    ];

    protected $casts = [
        'area' => 'decimal:2',
        'price' => 'decimal:2',
        'is_commercial' => 'boolean',
        'is_corner' => 'boolean',
    ];

    /**
     * Estate this plot belongs to
     */
    public function estate()
    {
        return $this->belongsTo(Estate::class);
    }

    /**
     * Plot type of this plot
     */
    public function plotType()
    {
        return $this->belongsTo(EstatePlotType::class, 'estate_plot_type_id');
    }

    /**
     * Inspections for this plot
     */
    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    /**
     * Purchase plots that include this plot
     */
    public function purchasePlots()
    {
        return $this->hasMany(PurchasePlot::class);
    }

   /**
     * Calculate the current price of the plot including any premiums and based on payment plan
     *
     * @param string $paymentPlanType The payment plan type ('outright', '6_months', '12_months')
     * @return float
     */
    public function getCurrentPrice($paymentPlanType = 'outright')
    {
        $estate = $this->estate;
        $plotType = $this->plotType;

        // First get the base price based on payment plan
        if ($plotType) {
            switch ($paymentPlanType) {
                case 'outright':
                    $basePrice = $plotType->outright_price;
                    break;
                case '6_months':
                    $basePrice = $plotType->six_month_price;
                    break;
                case '12_months':
                    $basePrice = $plotType->twelve_month_price;
                    break;
                default:
                    $basePrice = $this->price; // Fallback to the plot's price
            }
        } else {
            // If no plot type is found, use the plot's price
            $basePrice = $this->price;
        }

        // Then apply corner premium if applicable
        if ($this->is_corner && $estate) {
            $basePrice += ($basePrice * $estate->corner_plot_premium_percentage / 100);
        }

        // Then apply commercial premium if applicable
        if ($this->is_commercial && $estate) {
            $basePrice += ($basePrice * $estate->commercial_plot_premium_percentage / 100);
        }

        return $basePrice;
    }

    /**
     * Register media collections for the model
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('plot_images')
            ->useDisk('public');

        $this->addMediaCollection('plot_plan')
            ->singleFile();
    }
}