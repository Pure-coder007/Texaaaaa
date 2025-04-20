<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'state_id',
        'name',
        'status',
    ];

    /**
     * State this city belongs to
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Locations that belong to this city
     */
    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    /**
     * Estates in this city
     */
    public function estates()
    {
        return $this->hasMany(Estate::class);
    }
}