<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\TrackChanges;

class Driver extends Model
{
    use TrackChanges;
    protected $table = 'drivers';

    protected $fillable = [
        'user_id',
        'license_number',
        'contact_number',
        'license_expiry',
        'hire_date',
        'daily_boundary_target',
        'address',
        'emergency_contact',
        'emergency_phone',
        'driver_type',
        'driver_status',
        'created_by',
        'updated_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
