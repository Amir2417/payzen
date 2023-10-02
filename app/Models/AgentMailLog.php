<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentMailLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts    = [
        'agent_id'      => 'integer',
        'method'        => 'string',
        'subject'       => 'string',
        'message'       => 'string'
    ];

    public function agent() {
        return $this->belongsTo(Agent::class);
    }
}
