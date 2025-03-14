<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessTiming extends Model
{
    protected $fillable = [
        'business_id',
        'business_day_id', // Add 'business_day_id' to the fillable attributes
        'start_time',
        'close_time',
        'status',
        'is_deleted',
    ];
}
