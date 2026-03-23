<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\TrackChanges;

class Unit extends Model
{
    use TrackChanges;
    protected $table = 'units';

    protected $fillable = [
        'unit_number',
        'plate_number',
        'make',
        'model',
        'year',
        'status',
        'boundary_rate',
        'purchase_date',
        'purchase_cost',
        'color',
        'unit_type',
        'fuel_status',
        'coding_day',
        'driver_id',
        'secondary_driver_id',
        'gps_enabled',
        'dashcam_enabled',
        'latitude',
        'longitude',
        'current_location',
        'last_location_update',
    ];

    protected $casts = [
        'gps_enabled' => 'boolean',
        'dashcam_enabled' => 'boolean',
        'purchase_cost' => 'float',
        'boundary_rate' => 'float',
        'year' => 'integer',
    ];

    public function primaryDriver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function secondaryDriver()
    {
        return $this->belongsTo(User::class, 'secondary_driver_id');
    }

    public function boundaries()
    {
        return $this->hasMany(Boundary::class, 'unit_id');
    }

    public function maintenance()
    {
        return $this->hasMany(Maintenance::class, 'unit_id');
    }
}
