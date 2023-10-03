<?php

namespace App\Http\Controllers\Agent;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\Currency;
use App\Models\Admin\TransactionSetting;
use App\Models\AgentNotification;
use App\Models\AgentWallet;
use App\Models\TopupCategory;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileTopupController extends Controller
{
    public function index() {
        $page_title = "Mobile Topup";
        $topupCharge = TransactionSetting::where('slug','mobile_topup')->where('status',1)->first();
        $topupType = TopupCategory::active()->orderByDesc('id')->get();
        $transactions = Transaction::agentAuth()->mobileTopup()->latest()->take(10)->get();
        return view('agent.sections.mobile-top.index',compact("page_title",'topupCharge','transactions','topupType'));
    }
    public function payConfirm(Request $request){
        $request->validate([
            'topup_type' => 'required|string',
            'mobile_number' => 'required|min:10|max:13',
            'amount' => 'required|numeric|gt:0',

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
        $topUpType = $request->topup_type;
        $topup_type = TopupCategory::where('id', $topUpType)->first();
        $mobile_number = $request->mobile_number;
        $topupCharge = TransactionSetting::where('slug','mobile_topup')->where('status',1)->first();
        $agentWallet = AgentWallet::where('agent_id',$agent->id)->first();
        if(!$agentWallet){
            return back()->with(['error' => [' Wallet not found']]);
        }
        $baseCurrency = Currency::default();
        if(!$baseCurrency){
            return back()->with(['error' => ['Default currency not found']]);
        }
        $rate = $baseCurrency->rate;
        $minLimit =  $topupCharge->min_limit *  $rate;
        $maxLimit =  $topupCharge->max_limit *  $rate;
        if($amount < $minLimit || $amount > $maxLimit) {
            return back()->with(['error' => ['Please follow the transaction limit']]);
        }
        //charge calculations
        $fixedCharge = $topupCharge->fixed_charge *  $rate;
        $percent_charge = ($request->amount / 100) * $topupCharge->percent_charge;
        $total_charge = $fixedCharge + $percent_charge;
        $payable = $total_charge + $amount;
        if($payable > $agentWallet->balance ){
            return back()->with(['error' => ['Sorry, insuficiant balance']]);
        }
        try{
              $trx_id = 'MP'.getTrxNum();
              $sender = $this->insertSender( $trx_id,$agent,$agentWallet,$amount, $topup_type, $mobile_number,$payable);
              $this->insertSenderCharges( $fixedCharge,$percent_charge, $total_charge, $amount,$agent,$sender);
              //send sms notifications
              sendSms($agent,'MOBILE_TOPUP',[
                'amount'=> get_amount($amount,get_default_currency_code()),
                'topup_type' => $topup_type->name??'',
                'mobile_number' =>$mobile_number,
                'trx' => $trx_id,
                'time' =>  now()->format('Y-m-d h:i:s A'),
                'balance' => get_amount($agentWallet->balance,$agentWallet->currency->code),
            ]);

            return redirect()->route("agent.mobile.topup.index")->with(['success' => ['Mobile topup request send to admin successful']]);
        }catch(Exception $e) {
            return back()->with(['error' => [$e->getMessage()]]);
        }

    }
    public function insertSender( $trx_id,$agent,$agentWallet,$amount, $topup_type, $mobile_number,$payable) {
        $trx_id = $trx_id;
        $authWallet = $agentWallet;
        $afterCharge = ($authWallet->balance - $payable);
        $details =[
            'topup_type_id' => $topup_type->id??'',
            'topup_type_name' => $topup_type->name??'',
            'mobile_number' => $mobile_number,
            'topup_amount' => $amount??"",
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                       => $agent->id,
                'agent_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::MOBILETOPUP,
                'trx_id'                        => $trx_id,
                'request_amount'                => $amount,
                'payable'                       => $payable,
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::MOBILETOPUP," ")) . " Request To Admin",
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::SEND,
                'status'                        => 2,
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
    public function insertSenderCharges($fixedCharge,$percent_charge, $total_charge, $amount,$agent,$id) {
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
                'title'         =>"Mobile Topup ",
                'message'       => "Mobile Topup request send to admin " .$amount.' '.get_default_currency_code()." successful.",
                'image'         => files_asset_path('profile-default'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::MOBILE_TOPUP,
                'agent_id'  => $agent->id,
                'message'   => $notification_content,
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
}
