<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PboReferral extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'referrer_id',
        'referred_id',
        'status',
        'email',
        'converted_at',
        'expires_at',
    ];

    protected $casts = [
        'converted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Relationships
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_id');
    }
}