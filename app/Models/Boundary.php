<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\TrackChanges;

class Boundary extends Model
{
    use TrackChanges;
    protected $table = 'boundaries';

    protected $fillable = [
        'unit_id',
        'driver_id',
        'boundary_amount',
        'date',
        'status',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'boundary_amount' => 'float',
        'date' => 'date',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
}
