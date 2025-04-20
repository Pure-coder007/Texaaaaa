<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'city_id',
        'name',
        'description',
        'latitude',
        'longitude',
        'address',
        'postal_code',
        'status',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**
     * City this location belongs to
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Estates situated in this location
     */
    public function estates()
    {
        return $this->hasMany(Estate::class);
    }

}