<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardInformation extends Model
{
    protected $table = "card_information";

    protected $fillable = [
        'card_holder_name', // Add 'card_holder_name' to the fillable attributes
        'card_number',
        'expiry',
        'business_id',
        'user_id',
        'status',
        'is_deleted',
    ];
}
