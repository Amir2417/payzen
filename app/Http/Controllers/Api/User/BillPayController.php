<?php

namespace App\Http\Controllers\Api\User;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\Currency;
use App\Models\Admin\TransactionSetting;
use App\Models\BillPayCategory;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Notifications\User\BillPay\BillPayMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BillPayController extends Controller
{
    public function billPayInfo(){
        $user = auth()->user();
        $userWallet = UserWallet::where('user_id',$user->id)->get()->map(function($data){
            return[
                'balance' => getAmount($data->balance,2),
                'currency' => $data->currency->code,
            ];
        });
        $billPayCharge = TransactionSetting::where('slug','bill_pay')->where('status',1)->get()->map(function($data){
            return[
                'id' => $data->id,
                'slug' => $data->slug,
                'title' => $data->title,
                'fixed_charge' => getAmount($data->fixed_charge,2),
                'percent_charge' => getAmount($data->percent_charge,2),
                'min_limit' => getAmount($data->min_limit,2),
                'max_limit' => getAmount($data->max_limit,2),
                'monthly_limit' => getAmount($data->monthly_limit,2),
                'daily_limit' => getAmount($data->daily_limit,2),
            ];
        })->first();
        $billType = BillPayCategory::active()->orderByDesc('id')->get();
        $transactions = Transaction::auth()->billPay()->latest()->take(5)->get()->map(function($item){
            $statusInfo = [
                "success" =>      1,
                "pending" =>      2,
                "rejected" =>     3,
                ];
            return[
                'id' => $item->id,
                'trx' => $item->trx_id,
                'transaction_type' => $item->type,
                'request_amount' => getAmount($item->request_amount,2).' '.get_default_currency_code() ,
                'payable' => getAmount($item->payable,2).' '.get_default_currency_code(),
                'bill_type' =>$item->details->bill_type_name,
                'bill_number' =>$item->details->bill_number,
                'total_charge' => getAmount($item->charge->total_charge,2).' '.get_default_currency_code(),
                'current_balance' => getAmount($item->available_balance,2).' '.get_default_currency_code(),
                'status' => $item->stringStatus->value ,
                'date_time' => $item->created_at ,
                'status_info' =>(object)$statusInfo ,
                'rejection_reason' =>$item->reject_reason??"" ,

            ];
        });
        $data =[
            'base_curr' => get_default_currency_code(),
            'base_curr_rate' => get_default_currency_rate(),
            'billPayCharge'=> (object)$billPayCharge,
            'userWallet'=>  (object)$userWallet,
            'billTypes'=>  $billType,
            'transactions'   => $transactions,
        ];
        $message =  ['success'=>['Bill Pay Information']];
        return Helpers::success($data,$message);
    }
    public function billPayConfirmed(Request $request){
        $validator = Validator::make(request()->all(), [
            'bill_type' => 'required|string',
            'bill_number' => 'required|min:8',
            'amount' => 'required|numeric|gt:0',
            'sender_currency'   => "required|string|exists:currencies,code",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated  = $validator->validate();
        $basic_setting = BasicSettings::first();
        $user = auth()->user();
        if($basic_setting->kyc_verification){
            if( $user->kyc_verified == 0){
                $error = ['error'=>['Please submit kyc information!']];
                return Helpers::error($error);
            }elseif($user->kyc_verified == 2){
                $error = ['error'=>['Please wait before admin approved your kyc information']];
                return Helpers::error($error);
            }elseif($user->kyc_verified == 3){
                $error = ['error'=>['Admin rejected your kyc information, Please re-submit again']];
                return Helpers::error($error);
            }
        }
        $amount = $request->amount;
        $billType = $request->bill_type;
        $bill_type = BillPayCategory::where('id', $billType)->first();
        if(!$bill_type){
            $error = ['error'=>['Invalid bill type']];
            return Helpers::error($error);
        }
        $bill_number = $request->bill_number;
        $user = auth()->user();
        $billPayCharge = TransactionSetting::where('slug','bill_pay')->where('status',1)->first();
        $userWallet = UserWallet::auth()->whereHas("currency",function($q) use ($validated) {
            $q->where("code",$validated['sender_currency'])->active();
        })->active()->first();
        if(!$userWallet){
            $error = ['error'=>['wallet not found']];
            return Helpers::error($error);
        }
        $baseCurrency = $userWallet->currency;
        $charge_values = $this->billPayCharge($validated['amount'],$billPayCharge,$userWallet);
        
        if(!$baseCurrency){
            $error = ['error'=>['Default currency not found']];
            return Helpers::error($error);
        }
        $rate = $baseCurrency->rate;
        $minLimit =  $billPayCharge->min_limit *  $rate;
        $maxLimit =  $billPayCharge->max_limit *  $rate;
        if($amount < $minLimit || $amount > $maxLimit) {
            $error = ['error'=>['Please follow the transaction limit']];
            return Helpers::error($error);
        }
        //charge calculations
        $fixedCharge = $billPayCharge->fixed_charge *  $rate;
        $percent_charge = ($request->amount / 100) * $billPayCharge->percent_charge;
        $total_charge = $fixedCharge + $percent_charge;
        $payable = $total_charge + $amount;
        if($payable > $userWallet->balance ){
            $error = ['error'=>['Sorry, insufficient balance']];
            return Helpers::error($error);
        }
        try{
            $trx_id = 'BP'.getTrxNum();
            $notifyData = [
                'trx_id'  => $trx_id,
                'bill_type'  => @$bill_type->name,
                'bill_number'  => $bill_number,
                'request_amount'   => $amount,
                'charges'   => $total_charge,
                'payable'  => $payable,
                'current_balance'  => getAmount($userWallet->balance, 4),
                'status'  => "Pending",
              ];
               //send notifications
            $user = auth()->user();
            // dd($charge_values);
            $sender = $this->insertSender( $trx_id,$user,$userWallet,$amount, $bill_type, $bill_number,$payable,$charge_values);
            $this->insertSenderCharges( $fixedCharge,$percent_charge, $total_charge, $amount,$user,$sender);
            //send notifications
            if( $basic_setting->email_notification == true){
                // $user->notify(new BillPayMail($user,(object)$notifyData));
            }
            $message =  ['success'=>['Bill pay request send to admin successful']];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            dd($e->getMessage());
            $error = ['error'=>['Something is wrong, Please try again later']];
            return Helpers::error($error);
        }

    }
    public function billPayCharge($sender_amount,$charges,$userWallet) {
        $data['sender_amount']          = $sender_amount;
        $data['sender_currency']        = $userWallet->currency->code;
        $data['sender_currency_rate']   = $userWallet->currency->rate;
        $data['percent_charge']         = ($sender_amount / 100) * $charges->percent_charge ?? 0;
        $data['fixed_charge']           = $userWallet->currency->rate * $charges->fixed_charge ?? 0;
        $data['total_charge']           = $data['percent_charge'] + $data['fixed_charge'];
        $data['userWallet_balance']  = $userWallet->balance;
        $data['payable']                = $sender_amount + $data['total_charge'];
        return $data;
    }
    public function insertSender( $trx_id,$user,$userWallet,$amount, $bill_type,$bill_number,$payable,$charge_values) {
        $trx_id = $trx_id;
        
        $authWallet = $userWallet;
        $afterCharge = ($authWallet->balance - $charge_values['payable']);
        
        $details =[
            'bill_type_id' => $bill_type->id??'',
            'bill_type_name' => $bill_type->name??'',
            'bill_number' => $bill_number,
            'bill_amount' => $amount??"",
            'charges' => $charge_values,
        ];
        
        
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $authWallet->user->id,
                'user_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::BILLPAY,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charge_values['sender_amount'],
                'payable'                       => $charge_values['payable'],
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::BILLPAY," ")) . " Request To Admin",
                'details'                       => json_encode($details),
                'attribute'                     => PaymentGatewayConst::SEND,
                'status'                        => 2,
                'created_at'                    => now(),
            ]);
            $this->updateSenderWalletBalance($authWallet,$afterCharge);

            DB::commit();
        }catch(Exception $e) {
            dd($e->getMessage());
            DB::rollBack();
            $error = ['error'=>['Something is wrong, Please try again later']];
            return Helpers::error($error);
        }
        return $id;
    }
    public function updateSenderWalletBalance($authWalle,$afterCharge) {
        $authWalle->update([
            'balance'   => $afterCharge,
        ]);
    }
    public function insertSenderCharges($fixedCharge,$percent_charge, $total_charge, $amount,$user,$id) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $percent_charge,
                'fixed_charge'      =>$fixedCharge,
                'total_charge'      =>$total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>"Bill Pay ",
                'message'       => "Bill Pay request send to admin " .$amount.' '.get_default_currency_code()." successful.",
                'image'         => files_asset_path('profile-default'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::BILL_PAY,
                'user_id'  => $user->id,
                'message'   => $notification_content,
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>['Something is wrong, Please try again later']];
            return Helpers::error($error);
        }
    }
}
