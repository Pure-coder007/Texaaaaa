<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;

class Inspection extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'estate_id',
        'plot_id',
        'client_id',
        'scheduled_date',
        'scheduled_time',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'datetime:H:i',
    ];

    /**
     * Estate where the inspection is scheduled
     */
    public function estate()
    {
        return $this->belongsTo(Estate::class);
    }

    /**
     * Plot that is being inspected
     */
    public function plot()
    {
        return $this->belongsTo(Plot::class);
    }

    /**
     * Client scheduling the inspection
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

}