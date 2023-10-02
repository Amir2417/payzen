<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentAuthorization extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts    = [
        'agent_id'      => 'integer',
        'mobile'        => 'string',
        'code'          => 'integer',
        'token'         => 'string'
    ];

    public function agent() {
        return $this->belongsTo(Agent::class);
    }
}
