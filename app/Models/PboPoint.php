<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PboPoint extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'pbo_id',
        'points',
        'type',
        'description',
    ];


    // Relationships
    public function pbo()
    {
        return $this->belongsTo(User::class, 'pbo_id');
    }
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];
}
