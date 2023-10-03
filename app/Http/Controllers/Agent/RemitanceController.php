<?php

namespace App\Http\Controllers\Agent;

use App\Constants\GlobalConst;
use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Models\Admin\Currency;
use App\Models\Admin\ReceiverCounty;
use App\Models\Admin\TransactionSetting;
use App\Models\Agent;
use App\Models\AgentNotification;
use App\Models\AgentRecipient;
use App\Models\AgentWallet;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class RemitanceController extends Controller
{
    protected  $trx_id;
    public function __construct()
    {
        $this->trx_id = 'RT'.getTrxNum();
    }

    public function index() {
        $page_title = "Remittance";
        $exchangeCharge = TransactionSetting::where('slug','remittance')->where('status',1)->first();
        $receiverCountries = ReceiverCounty::active()->get();
        $transactions = Transaction::agentAuth()->remitance()->latest()->take(5)->get();
        return view('agent.sections.remittance.index',compact(
            "page_title",
            'exchangeCharge',
            'receiverCountries',
            'transactions'
        ));
    }
    public function confirmed(Request $request){
        $request->validate([
            'form_country'               =>'required',
            'to_country'                 =>'required',
            'transaction_type'           =>'required|string',
            'sender_recipient'           =>'required',
            'receiver_recipient'           =>'required',
            'send_amount'                =>"required|numeric",
            'receive_amount'             =>'required|numeric',

        ]);
        $exchangeCharge = TransactionSetting::where('slug','remittance')->where('status',1)->first();
        $user = auth()->user();

        $userWallet = AgentWallet::where('agent_id',$user->id)->first();
        if(!$userWallet){
            return back()->with(['error' => ['Sender wallet not found']]);
        }
        $baseCurrency = Currency::default();
        if(!$baseCurrency){
            return back()->with(['error' => ['Default currency not found']]);
        }
        $to_country = ReceiverCounty::where('id',$request->to_country)->first();
        if(!$to_country){
            return back()->with(['error' => ['Receiver country not found']]);
        }
        $receipient = AgentRecipient::auth()->sender()->where("id",$request->sender_recipient)->first();
        if(!$receipient){
            return back()->with(['error' => ['Sender Recipient is invalid']]);
        }
        $receiver_recipient = AgentRecipient::auth()->receiver()->where("id",$request->receiver_recipient)->first();
        if(!$receiver_recipient){
            return back()->with(['error' => ['Receiver  Recipient is invalid']]);
        }
        $base_rate = $baseCurrency->rate;
        $receiver_rate =$to_country->rate;
        $form_country =  $baseCurrency->country;
        $send_amount = $request->send_amount;
        $receive_amount = $request->receive_amount;
        $transaction_type = $request->transaction_type;
        $minLimit =  $exchangeCharge->min_limit *  $base_rate;
        $maxLimit =  $exchangeCharge->max_limit *  $base_rate;
        if($send_amount < $minLimit || $send_amount > $maxLimit) {
            return back()->with(['error' => ['Please follow the transaction limit']]);
        }

        //charge calculations
        $fixedCharge = $exchangeCharge->fixed_charge *  $base_rate;
        $percent_charge = ($send_amount / 100) * $exchangeCharge->percent_charge;
        $total_charge = $fixedCharge + $percent_charge;
        $payable = $total_charge + $send_amount;
        //receiver amount
        $receiver_rate = (float) $receiver_rate / (float)$base_rate;
        $receiver_amount = $receiver_rate * $send_amount;
        $receiver_will_get = $receiver_amount;
        if($payable > $userWallet->balance ){
            return back()->with(['error' => ['Sorry, insufficient balance']]);
        }
        try{
            if($transaction_type === Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                $receiver_user =  json_decode($receiver_recipient->details);
                $receiver_user =  $receiver_user->id;
                $receiver_wallet = AgentWallet::where('agent_id',$receiver_user)->first();

                if(!$receiver_wallet){
                    return back()->with(['error' => ['Receiver wallet not found']]);
                }
                $trx_id = $this->trx_id;
                $sender = $this->insertSender( $trx_id,$user,$userWallet,$send_amount,$receiver_will_get,$payable,$receipient,$form_country,$to_country,$transaction_type,$receiver_recipient);
                if($sender){
                     $this->insertSenderCharges( $fixedCharge,$percent_charge, $total_charge, $send_amount,$user,$sender,$receipient,$receiver_recipient);
                }
                $receiverTrans = $this->insertReceiver( $trx_id,$user,$userWallet,$send_amount,$receiver_will_get,$payable,$receipient,$form_country,$to_country,$transaction_type,$receiver_user,$receiver_wallet,$receiver_recipient);
                if($receiverTrans){
                     $this->insertReceiverCharges(  $fixedCharge,$percent_charge, $total_charge, $send_amount,$user,$receiverTrans,$receipient,$receiver_recipient);
                }
                session()->forget('sender_remittance_token');
                session()->forget('receiver_remittance_token');

            }else{
                $trx_id = $this->trx_id;
                $sender = $this->insertSender( $trx_id,$user,$userWallet,$send_amount,$receiver_will_get,$payable,$receipient,$form_country,$to_country,$transaction_type, $receiver_recipient);
                if($sender){
                     $this->insertSenderCharges( $fixedCharge,$percent_charge, $total_charge, $send_amount,$user,$sender,$receipient,$receiver_recipient);
                     sendSms($user,'SEND_REMITTANCE_AGENT',[
                        'form_country'  =>  $form_country,
                        'to_country'  =>  $to_country->country,
                        'transaction_type'  =>   ucwords(str_replace('-', ' ', @$transaction_type)),
                        'recipient'  =>  $receipient->fullname,
                        'receiver_recipient'  =>  $receiver_recipient->fullname,
                        'send_amount'=> get_amount($send_amount,get_default_currency_code()),
                        'receipient_amount'=> get_amount($receiver_will_get,$to_country->code),
                        'trx' => $trx_id,
                        'time' =>  now()->format('Y-m-d h:i:s A'),
                        'balance' => get_amount($userWallet->balance,$userWallet->currency->code),
                    ]);
                     session()->forget('sender_remittance_token');
                     session()->forget('receiver_remittance_token');
                }
            }

            return redirect()->route("agent.remittance.index")->with(['success' => ['Remittance Money send successfully']]);
        }catch(Exception $e) {
            return back()->with(['error' => [$e->getMessage()]]);
        }

    }
      //start transaction helpers
        //serder transaction
        public function insertSender($trx_id,$user,$userWallet,$send_amount,$receiver_will_get,$payable,$receipient,$form_country,$to_country,$transaction_type,$receiver_recipient) {
            $trx_id = $trx_id;
            $authWallet = $userWallet;
            $afterCharge = ($authWallet->balance - $payable);
            $details =[
                'recipient_amount' => $receiver_will_get,
                'receiver' => $receipient,
                'receiver_receiver' => $receiver_recipient,
                'form_country' => $form_country,
                'to_country' => $to_country,
                'remitance_type' => $transaction_type,
                'sender' => $user,
            ];
            if($transaction_type == Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER)){
                $status = 1;

            }else{
                $status = 2;
            }
            DB::beginTransaction();
            try{
                $id = DB::table("transactions")->insertGetId([
                    'agent_id'                       => $user->id,
                    'agent_wallet_id'                => $authWallet->id,
                    'payment_gateway_currency_id'   => null,
                    'type'                          => PaymentGatewayConst::SENDREMITTANCE,
                    'trx_id'                        => $trx_id,
                    'request_amount'                => $send_amount,
                    'payable'                       => $payable,
                    'available_balance'             => $afterCharge,
                    'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::SENDREMITTANCE," ")) . " To " .$receiver_recipient->fullname,
                    'details'                       => json_encode($details),
                    'attribute'                      =>PaymentGatewayConst::SEND,
                    'status'                        => $status,
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
        public function insertSenderCharges($fixedCharge,$percent_charge, $total_charge, $send_amount,$user,$sender,$receipient,$receiver_recipient) {


            DB::beginTransaction();
            try{
                DB::table('transaction_charges')->insert([
                    'transaction_id'    => $sender,
                    'percent_charge'    => $percent_charge,
                    'fixed_charge'      =>$fixedCharge,
                    'total_charge'      =>$total_charge,
                    'created_at'        => now(),
                ]);
                DB::commit();

                //notification
                $notification_content = [
                    'title'         =>"Send Remitance",
                    'message'       => "Send Remitance Request to ".$receiver_recipient->fullname.' ' .$send_amount.' '.get_default_currency_code()." successful",
                    'image'         => files_asset_path('profile-default'),
                ];

                AgentNotification::create([
                    'type'      => NotificationConst::SEND_REMITTANCE,
                    'agent_id'  => $user->id,
                    'message'   => $notification_content,
                ]);
                DB::commit();
            }catch(Exception $e) {
                DB::rollBack();
                throw new Exception($e->getMessage());
            }
        }
        //Receiver Transaction
        public function insertReceiver($trx_id,$user,$userWallet,$send_amount,$receiver_will_get,$payable,$receipient,$form_country,$to_country,$transaction_type,$receiver_user,$receiver_wallet,$receiver_recipient) {

            $trx_id = $trx_id;
            $receiverWallet = $receiver_wallet;
            $recipient_amount = ($receiverWallet->balance + $receiver_will_get);
            $details =[
                'recipient_amount' => $receiver_will_get,
                'receiver' => $receipient,
                'receiver_receiver' => $receiver_recipient,
                'form_country' => $form_country,
                'to_country' => $to_country,
                'remitance_type' => $transaction_type,
                'sender' => $user,
            ];
            DB::beginTransaction();
            try{
                $id = DB::table("transactions")->insertGetId([
                    'agent_id'                       => $receiver_user,
                    'agent_wallet_id'                => $receiverWallet->id,
                    'payment_gateway_currency_id'   => null,
                    'type'                          => PaymentGatewayConst::SENDREMITTANCE,
                    'trx_id'                        => $trx_id,
                    'request_amount'                => $send_amount,
                    'payable'                       => $payable,
                    'available_balance'             => $recipient_amount,
                    'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::RECEIVEREMITTANCE," ")) . " From " .$user->fullname,
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
        public function insertReceiverCharges( $fixedCharge,$percent_charge, $total_charge, $send_amount,$user,$receiverTrans,$receipient,$receiver_recipient) {
            DB::beginTransaction();

            try{
                DB::table('transaction_charges')->insert([
                    'transaction_id'    => $receiverTrans,
                    'percent_charge'    => $percent_charge,
                    'fixed_charge'      =>$fixedCharge,
                    'total_charge'      =>$total_charge,
                    'created_at'        => now(),
                ]);
                DB::commit();

                //notification
                $notification_content = [
                    'title'         =>"Send Remitance",
                    'message'       => "Send Remitance  from ".$user->fullname.' ' .$send_amount.' '.get_default_currency_code()." successful",
                    'image'         => files_asset_path('profile-default'),
                ];

                AgentNotification::create([
                    'type'      => NotificationConst::SEND_REMITTANCE,
                    'agent_id'  => $receiver_recipient->agent_id,
                    'message'   => $notification_content,
                ]);
                DB::commit();
            }catch(Exception $e) {

                DB::rollBack();
                throw new Exception($e->getMessage());
            }
        }
    //end transaction helpers
    public function getTokenForSender() {
        $data = request()->all();
        $in['receiver_country'] = $data['receiver_country'];
        $in['transacion_type'] = $data['transacion_type'];
        $in['sender_recipient'] = $data['sender_recipient'];
        $in['receiver_recipient'] = $data['receiver_recipient'];
        $in['sender_amount'] = $data['sender_amount'];
        $in['receive_amount'] = $data['receive_amount'];
        Session::put('sender_remittance_token',$in);
        return response()->json($data);

    }
    public function getTokenForReceiver() {
        $data = request()->all();
        $in['receiver_country'] = $data['receiver_country'];
        $in['transacion_type'] = $data['transacion_type'];
        $in['sender_recipient'] = $data['sender_recipient'];
        $in['receiver_recipient'] = $data['receiver_recipient'];
        $in['sender_amount'] = $data['sender_amount'];
        $in['receive_amount'] = $data['receive_amount'];
        Session::put('receiver_remittance_token',$in);
        return response()->json($data);

    }
    //sender filters
    public function getRecipientByCountry(Request $request){
        $receiver_country = $request->receiver_country;
        $transacion_type = $request->transacion_type;
        if( $transacion_type != null || $transacion_type != ''){
            $data['recipient'] =  AgentRecipient::auth()->sender()->where('country', $receiver_country)->where('type',$transacion_type)->get();

        }else{
            $data['recipient'] =  AgentRecipient::auth()->sender()->where('country', $receiver_country)->get();
        }
        return response()->json($data);
    }
    public function getRecipientByTransType(Request $request){
        $receiver_country = $request->receiver_country;
        $transacion_type = $request->transacion_type;
          $data['recipient'] =  AgentRecipient::auth()->sender()->where('country', $receiver_country)->where('type',$transacion_type)->get();
        return response()->json($data);
    }
    //Receiver filters
    public function getRecipientByCountryReceiver(Request $request){
        $receiver_country = $request->receiver_country;
        $transacion_type = $request->transacion_type;
        if( $transacion_type != null || $transacion_type != ''){
            $data['recipient'] =  AgentRecipient::auth()->receiver()->where('country', $receiver_country)->where('type',$transacion_type)->get();

        }else{
            $data['recipient'] =  AgentRecipient::auth()->receiver()->where('country', $receiver_country)->get();
        }
        return response()->json($data);
    }
    public function getRecipientByTransTypeReceiver(Request $request){
        $receiver_country = $request->receiver_country;
        $transacion_type = $request->transacion_type;
          $data['recipient'] =  AgentRecipient::auth()->receiver()->where('country', $receiver_country)->where('type',$transacion_type)->get();
        return response()->json($data);
    }
}
