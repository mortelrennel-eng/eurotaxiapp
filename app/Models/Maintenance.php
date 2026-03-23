<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\TrackChanges;

class Maintenance extends Model
{
    use TrackChanges;
    protected $table = 'maintenance';
 
    protected $fillable = [
        'unit_id',
        'maintenance_type',
        'description',
        'labor_cost',
        'odometer_reading',
        'date_started',
        'date_completed',
        'status',
        'mechanic_name',
        'parts_list',
        'cost',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'cost' => 'float',
        'maintenance_date' => 'date',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
