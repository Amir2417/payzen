<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentLoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip',
        'mac',
        'city',
        'country',
        'longitude',
        'latitude',
        'browser',
        'os',
        'timezone',
        'first_name','created_at'
    ];

    protected $casts    = [
        'id'            => 'integer',
        'agent_id'      => 'integer',
        'ip'            => 'string',
        'mac'           => 'string',
        'city'          => 'string',
        'country'       => 'string',
        'longitude'     => 'string',
        'latitude'      => 'string',
        'browser'       => 'string',
        'os'            => 'string',
        'timezone'      => 'string',
    ];
}
