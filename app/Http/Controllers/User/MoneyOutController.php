<?php

namespace App\Http\Controllers\User;

use Exception;
use App\Models\Agent;
use App\Models\UserWallet;
use App\Models\AgentWallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\Admin\Currency;
use App\Models\UserNotification;
use App\Models\AgentNotification;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\BasicSettings;
use App\Constants\NotificationConst;
use App\Http\Controllers\Controller;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\TransactionSetting;
use App\Notifications\User\MakePayment\SenderMail;
use App\Notifications\User\MakePayment\ReceiverMail;

class MoneyOutController extends Controller
{
    protected  $trx_id;
    public function __construct()
    {
        $this->trx_id = 'MP'.getTrxNum();
    }
    public function index() {
       
        $page_title = "Money Out";
        $currencies = Currency::active()->get();
        $makePaymentCharge = TransactionSetting::where('slug','money-out')->where('status',1)->first();
        $transactions = Transaction::auth()->MoneyOut()->latest()->take(10)->get();
        return view('user.sections.money-out.index',compact("page_title",'currencies','makePaymentCharge','transactions'));
    }
    public function checkUser(Request $request){
        $email = $request->email;
        $exist['data'] = Agent::where('email',$email)->first();

        $user = auth()->user();
        if(@$exist['data'] && $user->email == @$exist['data']->email){
            return response()->json(['own'=>'Can\'t transfer/request to your own']);
        }
        return response($exist);
    }
    public function confirmed(Request $request){
        
        $request->validate([
            'amount'            => 'required|numeric|gt:0',
            'email'             => 'required|email',
            'wallet_currency'   => 'required'
        ]);
        $basic_setting = BasicSettings::first();
        $wallet_currency = $request->wallet_currency;
        $user = auth()->user();
        if($basic_setting->kyc_verification){
            if( $user->kyc_verified == 0){
                return redirect()->route('user.profile.index')->with(['error' => ['Please submit kyc information']]);
            }elseif($user->kyc_verified == 2){
                return redirect()->route('user.profile.index')->with(['error' => ['Please wait before admin approved your kyc information']]);
            }elseif($user->kyc_verified == 3){
                return redirect()->route('user.profile.index')->with(['error' => ['Admin rejected your kyc information, Please re-submit again']]);
            }
        }
        $amount = $request->amount;
        $user = auth()->user();
        $makePaymentCharge = TransactionSetting::where('slug','money-out')->where('status',1)->first();
        $userWallet = UserWallet::auth()->whereHas("currency",function($q) use ($wallet_currency) {
            $q->where("code",$wallet_currency)->active();
        })->active()->first();
       
        if(!$userWallet){
            return back()->with(['error' => ['Sender wallet not found']]);
        }
        $baseCurrency = $userWallet->currency;
        if(!$baseCurrency){
            return back()->with(['error' => ['Default currency not found']]);
        }
        $rate = $baseCurrency->rate;
        $receiver = Agent::where('email', $request->email)->first();
        if(!$receiver){
            return back()->with(['error' => ['Receiver not exist']]);
        }
        $receiverWallet = AgentWallet::where('agent_id',$receiver->id)->first();
        if(!$receiverWallet){
            return back()->with(['error' => ['Receiver wallet not found']]);
        }

        $minLimit =  $makePaymentCharge->min_limit *  $rate;
        $maxLimit =  $makePaymentCharge->max_limit *  $rate;
        if($amount < $minLimit || $amount > $maxLimit) {
            return back()->with(['error' => ['Please follow the transaction limit']]);
        }
        //charge calculations
        $fixedCharge = $makePaymentCharge->fixed_charge *  $rate;
        $percent_charge = ($request->amount / 100) * $makePaymentCharge->percent_charge;
        $total_charge = $fixedCharge + $percent_charge;
        $payable = $total_charge + $amount;
        $recipient = $amount;
        $charges = $this->transferCharges($amount,$fixedCharge,$percent_charge,$total_charge,$userWallet);
        if($payable > $userWallet->balance ){
            return back()->with(['error' => ['Sorry, insufficient balance']]);
        }
        
        try{
            $trx_id = $this->trx_id;
            $sender = $this->insertSender( $trx_id,$user,$userWallet,$amount,$recipient,$payable,$receiver,$charges);
            if($sender){
                 $this->insertSenderCharges( $fixedCharge,$percent_charge, $total_charge, $amount,$user,$sender,$receiver);
            }
            //Sender notifications
            if( $basic_setting->email_notification == true){ 
                $notifyDataSender = [
                    'trx_id'  => $trx_id,
                    'title'  => "Money Out to @" . @$receiver->username." (".@$receiver->email.")",
                    'request_amount'  => getAmount($amount,4).' '.get_default_currency_code(),
                    'payable'   =>  getAmount($payable,4).' ' .get_default_currency_code(),
                    'charges'   => getAmount( $total_charge, 2).' ' .get_default_currency_code(),
                    'received_amount'  => getAmount( $recipient, 2).' ' .get_default_currency_code(),
                    'status'  => "Success",
                ];
                //sender notifications
                $user->notify(new SenderMail($user,(object)$notifyDataSender));
            }

            $receiverTrans = $this->insertReceiver( $trx_id,$user,$userWallet,$amount,$recipient,$payable,$receiver,$receiverWallet,$charges);
            if($receiverTrans){
                 $this->insertReceiverCharges( $fixedCharge,$percent_charge, $total_charge, $amount,$user,$receiverTrans,$receiver);
            }
            if( $basic_setting->email_notification == true){
                //Receiver notifications
                $notifyDataReceiver = [
                    'trx_id'  => $trx_id,
                    'title'  => "Money Out from @" .@$user->username." (".@$user->email.")",
                    'received_amount'  => getAmount( $recipient, 2).' ' .get_default_currency_code(),
                    'status'  => "Success",
                ];
                //send notifications
                $receiver->notify(new ReceiverMail($receiver,(object)$notifyDataReceiver));
            }

            return redirect()->route("user.withdraw.index")->with(['success' => ['Money Out successful to '.$receiver->fullname]]);
        }catch(Exception $e) {
            
            return back()->with(['error' => ["Something is wrong, please try again!"]]);
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
            throw new Exception($e->getMessage());
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
                'title'         =>"Make Payment",
                'message'       => "Payment to  ".$receiver->fullname.' ' .$amount.' '.get_default_currency_code()." successful",
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
            throw new Exception($e->getMessage());
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
                'agent_id'                       => $receiver->id,
                'agent_wallet_id'                => $receiverWallet->id,
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
            throw new Exception($e->getMessage());
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
                'title'         =>"Make Payment",
                'message'       => "Payment from  ".$user->fullname.' ' .$amount.' '.get_default_currency_code()." successful",
                'image'         => files_asset_path('profile-default'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::MAKE_PAYMENT,
                'agent_id'  => $receiver->id,
                'message'   => $notification_content,
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
    public function transferCharges($amount,$fixedCharge,$percent_charge,$total_charge,$userWallet) {
        $data['sender_amount']          = $amount;
        $data['sender_currency']        = $userWallet->currency->code;
        $data['receiver_amount']        = $amount;
        $data['receiver_currency']      = $userWallet->currency->code;
        $data['percent_charge']         = $percent_charge ?? 0;
        $data['fixed_charge']           = $fixedCharge ?? 0;
        $data['total_charge']           = $data['percent_charge'] + $data['fixed_charge'];
        $data['sender_wallet_balance']  = $userWallet->balance;
        $data['payable']                = $amount + $data['total_charge'];
        return $data;
    }
}
