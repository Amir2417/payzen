<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class MobileTopupLogs extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $statusInfo = [
            "success" =>      1,
            "pending" =>      2,
            "rejected" =>     3,
        ];
        return [
            'id' => $this->id,
            'trx' => $this->trx_id,
            'transaction_type' => $this->type,
            'request_amount' => getAmount($this->request_amount,2),
            'payable' => getAmount($this->payable,2),
            'topup_type' => $this->details->topup_type_name,
            'mobile_number' =>$this->details->mobile_number,
            'total_charge' => getAmount($this->charge->total_charge,2),
            'current_balance' => getAmount($this->available_balance,2),
            'status' => $this->stringStatus->value ,
            'date_time' => $this->created_at ,
            'status_info' =>(object)$statusInfo ,
            'rejection_reason' =>$this->reject_reason??"" ,
        ];
    }
}
