<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentKycData extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];

    protected $casts    = [
        'agent_id'      => 'integer',
        'data'          => 'object',
        'reject_reason' => 'string',
    ];
}
