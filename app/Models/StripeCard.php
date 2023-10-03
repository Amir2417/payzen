<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripeCard extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts        = [
        'id'                => 'integer',
        'user_id'           => 'integer',
        'name'              => 'string',
        'card_number'       => 'string',
        'expiration_date'   => 'string',
        'cvc_code'          => 'string',
        'status'            => 'integer',  
    ];
}
