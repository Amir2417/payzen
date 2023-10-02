<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentPasswordReset extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    protected $casts    = [
        'mobile'        => 'string',
        'code'          => 'integer',
        'token'         => 'string',
        'agent_id'      => 'integer',
    ];
    public function agent() {
        return $this->belongsTo(Agent::class)->select('id','username','email','firstname','lastname','mobile','full_mobile');
    }
}
