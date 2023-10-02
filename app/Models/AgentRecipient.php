<?php

namespace App\Models;

use App\Constants\GlobalConst;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgentRecipient extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts        = [
        'id'                => 'integer',
        'agent_id'          => 'integer',
        'country'           => 'integer',
        'type'              => 'string',
        'recipient_type'    => 'string',
        'alias'             => 'string',
        'firstname'         => 'string',
        'lastname'          => 'string',
        'mobile_code'       => 'string',
        'mobile'            => 'string',
        'city'              => 'string',
        'state'             => 'string',
        'address'           => 'string',
        'zip_code'          => 'string',
        'details'           => 'object',
    ];

    public function scopeAuth($query) {
        $query->where("agent_id",auth()->user()->id);
    }

    public function getFullnameAttribute()
    {

        return $this->firstname . ' ' . $this->lastname;
    }

    public function agent() {
        return $this->belongsTo(Agent::class);
    }

    public function receiver_country() {
        return $this->belongsTo(ReceiverCounty::class,'country');
    }

    public function scopeSender($query) {
        return $query->where("recipient_type",GlobalConst::SENDER);
    }
    
    public function scopeReceiver($query) {
        return $query->where("recipient_type",GlobalConst::RECEIVER);
    }
}
