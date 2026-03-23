<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\TrackChanges;

class Expense extends Model
{
    use TrackChanges;
    protected $table = 'expenses';

    protected $fillable = [
        'category',
        'description',
        'amount',
        'date',
        'receipt_path',
        'recorded_by',
        'notes',
        'reference_number',
        'unit_id',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'date' => 'date',
    ];
}
