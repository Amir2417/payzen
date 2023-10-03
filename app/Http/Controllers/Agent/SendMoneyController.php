<?php

namespace App\Http\Controllers\Agent;

use Exception;
use App\Models\User;
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
use App\Notifications\User\SendMoney\ReceiverMail;

class SendMoneyController extends Controller
{
    protected  $trx_id;
    public function __construct()
    {
        $this->trx_id = 'SM'.getTrxNum();
    }
    public function index() {
        $page_title = "Send Money";
        $sendMoneyCharge = TransactionSetting::where('slug','transfer')->where('status',1)->first();
        $transactions = Transaction::agentAuth()->senMoney()->latest()->take(10)->get();
        return view('agent.sections.send-money.index',compact("page_title",'sendMoneyCharge','transactions'));
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
            'amount' => 'required|numeric|gt:0',
            'email' => 'required'
        ]);
        $basic_setting = BasicSettings::first();
        $agent = userGuard()['user'];
        if($basic_setting->kyc_verification){
            if( $agent->kyc_verified == 0){
                return redirect()->route('agent.profile.index')->with(['error' => ['Please submit kyc information']]);
            }elseif($agent->kyc_verified == 2){
                return redirect()->route('agent.profile.index')->with(['error' => ['Please wait before admin approved your kyc information']]);
            }elseif($agent->kyc_verified == 3){
                return redirect()->route('agent.profile.index')->with(['error' => ['Admin rejected your kyc information, Please re-submit again']]);
            }
        }
        $amount = $request->amount;
        $sendMoneyCharge = TransactionSetting::where('slug','transfer')->where('status',1)->first();
        $agentWallet = AgentWallet::where('agent_id',$agent->id)->first();
        if(!$agentWallet){
            return back()->with(['error' => ['Sender wallet not found']]);
        }

        $baseCurrency = Currency::default();
        $rate = $baseCurrency->rate;
        if(!$baseCurrency){
            return back()->with(['error' => ['Default currency not found']]);
        }
        $receiver = Agent::where('email',$request->email)->first();
        if(!$receiver){
            return back()->with(['error' => ['Receiver not exist']]);
        }
        $receiverWallet = AgentWallet::where('agent_id',$receiver->id)->first();
        if(!$receiverWallet){
            return back()->with(['error' => ['Receiver wallet not found']]);
        }

        $minLimit =  $sendMoneyCharge->min_limit *  $rate;
        $maxLimit =  $sendMoneyCharge->max_limit *  $rate;
        if($amount < $minLimit || $amount > $maxLimit) {
            return back()->with(['error' => ['Please follow the transaction limit']]);
        }
        //charge calculations
        $fixedCharge = $sendMoneyCharge->fixed_charge *  $rate;
        $percent_charge = ($request->amount / 100) * $sendMoneyCharge->percent_charge;
        $total_charge = $fixedCharge + $percent_charge;
        $payable = $total_charge + $amount;
        $recipient = $amount;
        if($payable > $agentWallet->balance ){
            return back()->with(['error' => ['Sorry, insufficient balance']]);
        }

        try{
            $trx_id = $this->trx_id;
            $sender = $this->insertSender( $trx_id,$agent,$agentWallet,$amount,$recipient,$payable,$receiver);
            if($sender){
                 $this->insertSenderCharges( $fixedCharge,$percent_charge, $total_charge, $amount,$agent,$sender,$receiver);
                 if( $basic_setting->email_notification == true){
                    $notifyDataSender = [
                        'trx_id'  => $trx_id,
                        'title'  => "Send Money to @" . @$receiver->username." (".@$receiver->email.")",
                        'request_amount'  => getAmount($amount,4).' '.get_default_currency_code(),
                        'payable'   =>  getAmount($payable,4).' ' .get_default_currency_code(),
                        'charges'   => getAmount( $total_charge, 2).' ' .get_default_currency_code(),
                        'received_amount'  => getAmount( $recipient, 2).' ' .get_default_currency_code(),
                        'status'  => "Success",
                    ];
                    //sender notifications
                    $agent->notify(new SenderMail($agent,(object)$notifyDataSender));
                }
            }
            $receiverTrans = $this->insertReceiver( $trx_id,$agent,$agentWallet,$amount,$recipient,$payable,$receiver,$receiverWallet);
            if($receiverTrans){
                 $this->insertReceiverCharges( $fixedCharge,$percent_charge, $total_charge, $amount,$agent,$receiverTrans,$receiver);
                 if( $basic_setting->email_notification == true){
                    $notifyDataReceiver = [
                        'trx_id'  => $trx_id,
                        'title'  => "Received Money from @" .@$agent->username." (".@$agent->email.")",
                        'received_amount'  => getAmount( $recipient, 2).' ' .get_default_currency_code(),
                        'status'  => "Success",
                    ];
                    //send notifications
                    $receiver->notify(new ReceiverMail($receiver,(object)$notifyDataReceiver));
                }
            }

            return redirect()->route("agent.send.money.index")->with(['success' => ['Send Money successful to '.$receiver->fullname]]);
        }catch(Exception $e) {
            return back()->with(['error' => [$e->getMessage()]]);
        }

    }
    //sender transaction
    public function insertSender($trx_id,$agent,$agentWallet,$amount,$recipient,$payable,$receiver) {
        $trx_id = $trx_id;
        $authWallet = $agentWallet;
        $afterCharge = ($authWallet->balance - $payable);
        $details =[
            'recipient_amount' => $recipient,
            'receiver' => $receiver,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                       => $agent->id,
                'agent_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::TYPETRANSFERMONEY,
                'trx_id'                        => $trx_id,
                'request_amount'                => $amount,
                'payable'                       => $payable,
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPETRANSFERMONEY," ")) . " To " .$receiver->fullname,
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
    public function insertSenderCharges($fixedCharge,$percent_charge, $total_charge, $amount,$agent,$id,$receiver) {
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
                'title'         =>"Transfer Money",
                'message'       => "Transfer Money to  ".$receiver->fullname.' ' .$amount.' '.get_default_currency_code()." successful",
                'image'         => files_asset_path('profile-default'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::TRANSFER_MONEY,
                'agent_id'  => $agent->id,
                'message'   => $notification_content,
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
    //Receiver Transaction
    public function insertReceiver($trx_id,$agent,$agentWallet,$amount,$recipient,$payable,$receiver,$receiverWallet) {
        $trx_id = $trx_id;
        $receiverWallet = $receiverWallet;
        $recipient_amount = ($receiverWallet->balance + $recipient);
        $details =[
            'sender_amount' => $amount,
            'sender' => $agent,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                       => $receiver->id,
                'agent_wallet_id'                => $receiverWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::TYPETRANSFERMONEY,
                'trx_id'                        => $trx_id,
                'request_amount'                => $amount,
                'payable'                       => $payable,
                'available_balance'             => $recipient_amount,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPETRANSFERMONEY," ")) . " From " .$agent->fullname,
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
    public function insertReceiverCharges($fixedCharge,$percent_charge, $total_charge, $amount,$agent,$id,$receiver) {
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
                'title'         =>"Transfer Money",
                'message'       => "Transfer Money from  ".$agent->fullname.' ' .$amount.' '.get_default_currency_code()." successful",
                'image'         => files_asset_path('profile-default'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::TRANSFER_MONEY,
                'agent_id'  => $receiver->id,
                'message'   => $notification_content,
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
}
