<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $fillable = [
        'logo',
        'cover',
        'address',
        'city_id',
        'state_id',
        'zipcode',
        'description',
        'status',
        'is_deleted',
    ];
}
