<?php

namespace App\Http\Controllers\Api\User;

use Exception;
use App\Models\Agent;
use App\Models\UserWallet;
use App\Models\AgentQrCode;
use App\Models\AgentWallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\UserNotification;
use App\Http\Helpers\Api\Helpers;
use App\Models\AgentNotification;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\BasicSettings;
use App\Constants\NotificationConst;
use App\Http\Controllers\Controller;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\TransactionSetting;
use Illuminate\Support\Facades\Validator;
use App\Notifications\User\SendMoney\SenderMail;
use App\Notifications\User\MakePayment\ReceiverMail;

class MoneyOutController extends Controller
{
    protected  $trx_id;
    public function __construct()
    {
        $this->trx_id = 'MO'.getTrxNum();
    }
    public function moneyOutInfo(){
        $user = auth()->user();
        $makePaymentcharge = TransactionSetting::where('slug','money-out')->where('status',1)->get()->map(function($data){
            return[
                'id' => $data->id,
                'slug' => $data->slug,
                'title' => $data->title,
                'fixed_charge' => getAmount($data->fixed_charge,2),
                'percent_charge' => getAmount($data->percent_charge,2),
                'min_limit' => getAmount($data->min_limit,2),
                'max_limit' => getAmount($data->max_limit,2),
            ];
        })->first();
        $transactions = Transaction::auth()->MoneyOut()->latest()->take(10)->get()->map(function($item){
            $statusInfo = [
                "success" =>      1,
                "pending" =>      2,
                "rejected" =>     3,
                ];
                if($item->attribute == payment_gateway_const()::SEND){
                    return[
                        'id' => @$item->id,
                        'type' =>$item->attribute,
                        'trx' => @$item->trx_id,
                        'transaction_type' => $item->type,
                        'transaction_heading' => "Money Out to @" . @$item->details->receiver->username." (".@$item->details->receiver->email.")",
                        'request_amount' => getAmount(@$item->request_amount,2) ,
                        'total_charge' => getAmount(@$item->charge->total_charge,2),
                        'payable' => getAmount(@$item->payable,2),
                        'recipient_received' => getAmount(@$item->details->recipient_amount,2),
                        'current_balance' => getAmount(@$item->available_balance,2),
                        'status' => @$item->stringStatus->value ,
                        'date_time' => @$item->created_at ,
                        'status_info' =>(object)@$statusInfo ,
                    ];
                }elseif($item->attribute == payment_gateway_const()::RECEIVED){
                    return[
                        'id' => @$item->id,
                        'type' =>$item->attribute,
                        'trx' => @$item->trx_id,
                        'transaction_type' => $item->type,
                        'transaction_heading' => "Received Money from @" .@$item->details->sender->username." (".@$item->details->sender->email.")",
                        'recipient_received' => getAmount(@$item->request_amount,2),
                        'current_balance' => getAmount(@$item->available_balance,2),
                        'status' => @$item->stringStatus->value ,
                        'date_time' => @$item->created_at ,
                        'status_info' =>(object)@$statusInfo ,
                    ];

                }

        });
        $userWallet = UserWallet::where('user_id',$user->id)->get()->map(function($data){
            return[
                'balance' => getAmount($data->balance,2),
                'rate' => $data->currency->rate,
                'currency' => $data->currency->code,
            ];
        });
        $data =[
            'base_curr' => get_default_currency_code(),
            'base_curr_rate' => get_default_currency_rate(),
            'makePaymentcharge'=> (object)$makePaymentcharge,
            'userWallet'=>  (object)$userWallet,
            'transactions'   => $transactions,
        ];
        $message =  ['success'=>['Money Out Information']];
        return Helpers::success($data,$message);
    }
    public function checkAgent(Request $request){
        $validator = Validator::make(request()->all(), [
            'email'     => "required|email",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $exist = Agent::where('email',$request->email)->first();
        if( !$exist){
            $error = ['error'=>['Agent not found']];
            return Helpers::error($error);
        }
        $user = auth()->user();
        if(@$exist && $user->email == @$exist->email){
             $error = ['error'=>['Can\'t transfer/request to your own']];
            return Helpers::error($error);
        }
        $data =[
            'exist_agent'   => $exist,
            ];
        $message =  ['success'=>['Valid Agent for transaction.']];
        return Helpers::success($data,$message);
    }
    public function qrScan(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'qr_code'     => "required",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $qr_code = $request->qr_code;
        $qrCode = AgentQrCode::where('qr_code',$qr_code)->first();
        if(!$qrCode){
            $error = ['error'=>['Invalid Request!']];
            return Helpers::error($error);
        }
        $user = Agent::find($qrCode->merchant_id);
        if(!$user){
            $error = ['error'=>['Agent not found']];
            return Helpers::error($error);
        }
        if( $user->email == auth()->user()->email){
            $error = ['error'=>['Can\'t transfer/request to your own']];
            return Helpers::error($error);
        }
        $data =[
            'agent_email'   => $user->email,
            ];
        $message =  ['success'=>['QR Scan Result.']];
        return Helpers::success($data,$message);
    }
    public function confirmedPayment(Request $request){
        $validator = Validator::make(request()->all(), [
            'amount' => 'required|numeric|gt:0',
            'email' => 'required|email',
            'wallet_currency'   => 'required'
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $basic_setting = BasicSettings::first();
        $wallet_currency = $request->wallet_currency;
        $user = auth()->user();
        if($basic_setting->kyc_verification){
            if( $user->kyc_verified == 0){
                $error = ['error'=>['Please submit kyc information']];
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
        $user = auth()->user();
        $makePaymentCharge = TransactionSetting::where('slug','money-out')->where('status',1)->first();
        $userWallet = UserWallet::auth()->whereHas("currency",function($q) use ($wallet_currency) {
            $q->where("code",$wallet_currency)->active();
        })->active()->first();
        if(!$userWallet){
            $error = ['error'=>['Sender wallet not found']];
            return Helpers::error($error);
        }
        $baseCurrency = $userWallet->currency;
        if(!$baseCurrency){
            $error = ['error'=>['Default currency not found']];
            return Helpers::error($error);
        }
        $rate = $baseCurrency->rate;
        $receiver = Agent::where('email', $request->email)->first();
        if(!$receiver){
            $error = ['error'=>['Receiver not exist']];
            return Helpers::error($error);
        }
        $receiverWallet = AgentWallet::where('agent_id',$receiver->id)->first();
        if(!$receiverWallet){
            $error = ['error'=>['Receiver wallet not found']];
            return Helpers::error($error);
        }
        $minLimit =  $makePaymentCharge->min_limit *  $rate;
        $maxLimit =  $makePaymentCharge->max_limit *  $rate;
        if($amount < $minLimit || $amount > $maxLimit) {
            $error = ['error'=>['Please follow the transaction limit']];
            return Helpers::error($error);
        }
        //charge calculations
        $fixedCharge = $makePaymentCharge->fixed_charge *  $rate;
        $percent_charge = ($request->amount / 100) * $makePaymentCharge->percent_charge;
        $total_charge = $fixedCharge + $percent_charge;
        $payable = $total_charge + $amount;
        $recipient = $amount;
        $charges = $this->transferCharges($amount,$fixedCharge,$percent_charge,$total_charge,$userWallet);
        if($payable > $userWallet->balance ){
            $error = ['error'=>['Sorry, insufficient balance']];
            return Helpers::error($error);
        }
        try{
            $trx_id = $this->trx_id;
            //sender notifications
            $notifyDataSender = [
                'trx_id'  => $trx_id,
                'title'  => "Money Out to @" . @$receiver->username." (".@$receiver->email.")",
                'request_amount'  => getAmount($amount,4),
                'payable'   =>  getAmount($payable,4).' ' .get_default_currency_code(),
                'charges'   => getAmount( $total_charge, 2).' ' .get_default_currency_code(),
                'received_amount'  => getAmount( $recipient, 2).' ' .get_default_currency_code(),
                'status'  => "Success",
              ];

            $sender = $this->insertSender( $trx_id,$user,$userWallet,$amount,$recipient,$payable,$receiver,$charges);
            if($sender){
                 $this->insertSenderCharges( $fixedCharge,$percent_charge, $total_charge, $amount,$user,$sender,$receiver);
            }
            if( $basic_setting->email_notification == true){
                $user->notify(new SenderMail($user,(object)$notifyDataSender));
            }
            //Receiver notifications
            $notifyDataReceiver = [
                'trx_id'  => $trx_id,
                'title'  => "Money Out from @" .@$user->username." (".@$user->email.")",
                'received_amount'  => getAmount( $recipient, 2).' ' .get_default_currency_code(),
                'status'  => "Success",
              ];

            $receiverTrans = $this->insertReceiver( $trx_id,$user,$userWallet,$amount,$recipient,$payable,$receiver,$receiverWallet,$charges);
            if($receiverTrans){
                 $this->insertReceiverCharges( $fixedCharge,$percent_charge, $total_charge, $amount,$user,$receiverTrans,$receiver);
            }
            //send notifications
            if( $basic_setting->email_notification == true){
                $receiver->notify(new ReceiverMail($receiver,(object)$notifyDataReceiver));
            }
            $message = ['success'=>['Money Out successful to '.$receiver->fullname]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            $error = ['error'=>['Something is wrong, please try again!']];
            return Helpers::error($error);
        }

    }

    //sender transaction
    public function insertSender($trx_id,$user,$userWallet,$amount,$recipient,$payable,$receiver,$charges) {
        $trx_id = $trx_id;
        $authWallet = $userWallet;
        $afterCharge = ($authWallet->balance - $payable);
        $details =[
            'receiver_username'=> $receiver->username,
            'receiver_email'=> $receiver->email,
            'sender_username'=> $user->username,
            'sender_email'=> $user->email,
            'charges' => $charges,
            'recipient_amount' => $recipient,
            'receiver' => $receiver,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $user->id,
                'user_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::TYPEMONEYOUT,
                'trx_id'                        => $trx_id,
                'request_amount'                => $amount,
                'payable'                       => $payable,
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPEMONEYOUT," ")) . " To " .$receiver->fullname,
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::SEND,
                'status'                        => true,
                'created_at'                    => now(),
            ]);
            $this->updateSenderWalletBalance($authWallet,$afterCharge);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>['Something is wrong, please try again!']];
            return Helpers::error($error);
        }
        return $id;
    }
    public function updateSenderWalletBalance($authWalle,$afterCharge) {
        $authWalle->update([
            'balance'   => $afterCharge,
        ]);
    }
    public function insertSenderCharges($fixedCharge,$percent_charge, $total_charge, $amount,$user,$id,$receiver) {
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
                'title'         =>"Money Out",
                'message'       => "Payment to  ".$receiver->fullname.' ' .$amount." successful",
                'image'         => files_asset_path('profile-default'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::WITHDRAW,
                'user_id'  => $user->id,
                'message'   => $notification_content,
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>['Something is wrong, please try again!']];
            return Helpers::error($error);
        }
    }
    //Receiver Transaction
    public function insertReceiver($trx_id,$user,$userWallet,$amount,$recipient,$payable,$receiver,$receiverWallet,$charges) {
        $trx_id = $trx_id;
        $receiverWallet = $receiverWallet;
        $recipient_amount = ($receiverWallet->balance + $recipient);
        $details =[
            'receiver_username'=> $receiver->username,
            'receiver_email'=> $receiver->email,
            'sender_username'=> $user->username,
            'sender_email'=> $user->email,
            'charges' => $charges,
            'recipient_amount' => $recipient,
            'receiver' => $receiver,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'merchant_id'                       => $receiver->id,
                'merchant_wallet_id'                => $receiverWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::TYPEMONEYOUT,
                'trx_id'                        => $trx_id,
                'request_amount'                => $amount,
                'payable'                       => $payable,
                'available_balance'             => $recipient_amount,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPEMONEYOUT," ")) . " From " .$user->fullname,
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::RECEIVED,
                'status'                        => true,
                'created_at'                    => now(),
            ]);
            $this->updateReceiverWalletBalance($receiverWallet,$recipient_amount);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>['Something is wrong, please try again!']];
            return Helpers::error($error);
        }
        return $id;
    }
    public function updateReceiverWalletBalance($receiverWallet,$recipient_amount) {
        $receiverWallet->update([
            'balance'   => $recipient_amount,
        ]);
    }
    public function insertReceiverCharges($fixedCharge,$percent_charge, $total_charge, $amount,$user,$id,$receiver) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $percent_charge,
                'fixed_charge'      =>$fixedCharge,
                'total_charge'      =>0,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>"Money Out",
                'message'       => "Payment from  ".$user->fullname.' ' .$amount." successful",
                'image'         => files_asset_path('profile-default'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::WITHDRAW,
                'merchant_id'  => $receiver->id,
                'message'   => $notification_content,
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>['Something is wrong, please try again!']];
            return Helpers::error($error);
        }
    }
    public function transferCharges($amount,$fixedCharge,$percent_charge,$total_charge,$userWallet) {
        $data['sender_amount']          = $amount;
        $data['sender_currency']        = $userWallet->currency->code;
        $data['receiver_amount']        = $amount;
        $data['receiver_currency']      = $userWallet->currency->code;
        $data['percent_charge']         = $percent_charge ?? 0;
        $data['fixed_charge']           = $fixedCharge ?? 0;
        $data['total_charge']           = $total_charge;
        $data['sender_wallet_balance']  = $userWallet->balance;
        $data['payable']                = $amount + $data['total_charge'];
        return $data;
    }
}
