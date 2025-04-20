<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Estate extends Model implements HasMedia
{
    use HasFactory, HasUuids, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name',
        'description',
        'city_id',
        'location_id',
        'address',
        'total_area',
        'status',
        'manager_id',
        'corner_plot_premium_percentage',
        'commercial_plot_premium_percentage',
        'faq',
        'terms',
        'refund_policy',
    ];

    protected $casts = [
        'total_area' => 'decimal:2',
        'corner_plot_premium_percentage' => 'decimal:2',
        'commercial_plot_premium_percentage' => 'decimal:2',
        'faq' => 'json',
        'terms' => 'json',
        'refund_policy' => 'json',
    ];

    /**
     * City this estate is located in
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Location of this estate
     */
    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Manager of this estate
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Plot types defined for this estate
     */
    public function plotTypes()
    {
        return $this->hasMany(EstatePlotType::class);
    }

    /**
     * Plots in this estate
     */
    public function plots()
    {
        return $this->hasMany(Plot::class);
    }

    /**
     * Promotions offered by this estate
     */
    public function promos()
    {
        return $this->hasMany(Promo::class);
    }

    /**
     * Promo codes offered by this estate
     */
    public function promoCodes()
    {
        return $this->hasMany(PromoCode::class);
    }

    /**
     * Inspections hosted at this estate
     */
    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    /**
     * Purchases made for plots in this estate
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Register media collections for the model
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('estate_images')
            ->useDisk('public');

        $this->addMediaCollection('featured_image')
            ->singleFile();

        $this->addMediaCollection('estate_plans')
            ->useDisk('public');

        $this->addMediaCollection('site_plan')
            ->singleFile();

        $this->addMediaCollection('documents')
            ->useDisk('public');
    }
}