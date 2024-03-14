<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSocialMediaLink extends Model
{
    protected $fillable = [
        'business_id',
        'link',
        'status',
        'is_deleted',
    ];
}
