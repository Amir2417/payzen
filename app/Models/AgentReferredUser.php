<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentReferredUser extends Model
{
    use HasFactory;

    use HasFactory;

    protected $guarded = ['id'];

    protected $casts        = [
        'id'                => 'integer',
        'refer_agent_id'    => 'integer',
        'new_agent_id'      => 'integer',
    ];

    public function referAgent() {
        return $this->belongsTo(Agent::class,'refer_agent_id');
    }

    public function agent() {
        return $this->belongsTo(Agent::class,'new_agent_id');
    }
}
