<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'country_id',
        'name',
        'code',
        'status',
    ];

    /**
     * Country this state belongs to
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Cities that belong to this state
     */
    public function cities()
    {
        return $this->hasMany(City::class);
    }
}