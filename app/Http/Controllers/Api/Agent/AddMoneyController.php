<?php

namespace App\Http\Controllers\Api\Agent;

use App\Models\AgentWallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\PaymentGateway;
use App\Traits\PaymentGateway\Manual;
use App\Traits\PaymentGateway\Stripe;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\PaymentGatewayCurrency;
use App\Http\Helpers\Api\Helpers;

class AddMoneyController extends Controller
{
    use Stripe,Manual;
    public function addMoneyInformation(){
        $user = auth()->user();
        $agentWallet = AgentWallet::where('agent_id',$user->id)->get()->map(function($data){
            return[
                'balance'   => getAmount($data->balance,2),
                'currency'  => $data->currency->code,
                'rate'      => $data->currency->rate,
            ];
            });
            $transactions = Transaction::agentAuth()->addMoney()->latest()->take(5)->get()->map(function($item){
                $statusInfo = [
                    "success" =>      1,
                    "pending" =>      2,
                    "rejected" =>     3,
                    ];
                return[
                    'id' => $item->id,
                    'trx' => $item->trx_id,
                    'gateway_name' => $item->currency->name,
                    'transaction_type' => $item->type,
                    'request_amount' => getAmount($item->request_amount,4),
                    'payable' => getAmount($item->payable,4).' '.$item->currency->currency_code,
                    'exchange_rate' => '1 ' .get_default_currency_code().' = '.getAmount($item->currency->rate,4).' '.$item->currency->currency_code,
                    'total_charge' => getAmount($item->charge->total_charge,4).' '.$item->currency->currency_code,
                    'current_balance' => getAmount($item->available_balance,4),
                    'status' => $item->stringStatus->value ,
                    'date_time' => $item->created_at ,
                    'status_info' =>(object)$statusInfo ,
                    'rejection_reason' =>$item->reject_reason??"" ,

                ];
                });


        $gateways = PaymentGateway::where('status', 1)->where('slug', PaymentGatewayConst::add_money_slug())->get()->map(function($gateway){
            $currencies = PaymentGatewayCurrency::where('payment_gateway_id',$gateway->id)->get()->map(function($data){
              return[
                'id' => $data->id,
                'payment_gateway_id' => $data->payment_gateway_id,
                'type' => $data->gateway->type,
                'name' => $data->name,
                'alias' => $data->alias,
                'currency_code' => $data->currency_code,
                'currency_symbol' => $data->currency_symbol,
                'image' => $data->image,
                'min_limit' => getAmount($data->min_limit,4),
                'max_limit' => getAmount($data->max_limit,4),
                'percent_charge' => getAmount($data->percent_charge,4),
                'fixed_charge' => getAmount($data->fixed_charge,4),
                'rate' => getAmount($data->rate,4),
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,
              ];

            });
            return[
                'id' => $gateway->id,
                'image' => $gateway->image,
                'slug' => $gateway->slug,
                'code' => $gateway->code,
                'type' => $gateway->type,
                'alias' => $gateway->alias,
                'supported_currencies' => $gateway->supported_currencies,
                'status' => $gateway->status,
                'currencies' => $currencies

            ];
        });
        $data =[
            'base_curr'    => get_default_currency_code(),
            'base_curr_rate' => get_default_currency_rate(),
            'default_image'    => "public/backend/images/default/default.webp",
            "image_path"  =>  "public/backend/images/payment-gateways",
            'agentWallet'   =>   (object)$agentWallet,
            'gateways'   => $gateways,
            'transactionss'   =>   $transactions,
            ];
            $message =  ['success'=>['Add Money Information!']];
            return Helpers::success($data,$message);
    }
}
