<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class MerchantPaymentLogs extends JsonResource
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
            'transaction_heading' => "Payment Money to @" . @$this->details->payment_to." (".@$this->details->pay_type.")",
            'request_amount' => getAmount($this->request_amount,2) ,
            'payable' => getAmount($this->payable,2),
            'env_type' => $this->details->env_type,
            'sender_amount' => getAmount($this->details->charges->sender_amount,2),
            'recipient' =>  $this->details->receiver_username,
            'recipient_amount' => getAmount($this->details->charges->receiver_amount,2),
            'status' => $this->stringStatus->value ,
            'date_time' => $this->created_at ,
            'status_info' =>(object)$statusInfo ,
            'rejection_reason' =>$this->reject_reason??"" ,
        ];
    }
}
