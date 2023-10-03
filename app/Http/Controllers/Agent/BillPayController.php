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
use App\Models\BillPayCategory;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillPayController extends Controller
{
    public function index() {
        $page_title = "Bill Pay";
        $billPayCharge = TransactionSetting::where('slug','bill_pay')->where('status',1)->first();
        $transactions = Transaction::agentAuth()->billPay()->latest()->take(10)->get();
        return view('agent.sections.bill-pay.index',compact("page_title",'billPayCharge','transactions'));
    }
    public function fetchBillTypes(Request $request){
        $bill_category = $request->bill_category;
        $getBillTypes = getBillPayCategories($bill_category);
        return response()->json($getBillTypes);
    }
    public function payConfirm(Request $request){
        $request->validate([
            'bill_type' => 'required|string',
            'customer_identifier' => 'required',
            'amount' => 'required|numeric|gt:0',

        ]);
        $basic_setting = BasicSettings::first();
        $user = auth()->user();
        if($basic_setting->kyc_verification){
            if( $user->kyc_verified == 0){
                return redirect()->route('agent.profile.index')->with(['error' => ['Please submit kyc information']]);
            }elseif($user->kyc_verified == 2){
                return redirect()->route('agent.profile.index')->with(['error' => ['Please wait before admin approved your kyc information']]);
            }elseif($user->kyc_verified == 3){
                return redirect()->route('agent.profile.index')->with(['error' => ['Admin rejected your kyc information, Please re-submit again']]);
            }
        }
        $amount = $request->amount;
        $bill_type =  $request->bill_type;
        $customer_identifier = $request->customer_identifier;
        $user = auth()->user();
        $billPayCharge = TransactionSetting::where('slug','bill_pay')->where('status',1)->first();
        $userWallet = AgentWallet::where('agent_id',$user->id)->first();
        if(!$userWallet){
            return back()->with(['error' => ['Sender wallet not found']]);
        }
        $baseCurrency = Currency::default();
        $rate = $baseCurrency->rate;
        if(!$baseCurrency){
            return back()->with(['error' => ['Default currency not found']]);
        }

        $minLimit =  $billPayCharge->min_limit *  $rate;
        $maxLimit =  $billPayCharge->max_limit *  $rate;
        if($amount < $minLimit || $amount > $maxLimit) {
            return back()->with(['error' => ['Please follow the transaction limit']]);
        }
        //charge calculations
        $fixedCharge = $billPayCharge->fixed_charge *  $rate;
        $percent_charge = ($request->amount / 100) * $billPayCharge->percent_charge;
        $total_charge = $fixedCharge + $percent_charge;
        $payable = $total_charge + $amount;
        if($payable > $userWallet->balance ){
            return back()->with(['error' => ['Sorry, insufficient balance']]);
        }
        try{
            $apiBillPay = payBill($bill_type, $customer_identifier,$amount);
            if( $apiBillPay['status'] == 'success'){
                $trx_id = 'BP'.getTrxNum();
                $sender = $this->insertSender( $trx_id,$user,$userWallet,$amount, $bill_type, $customer_identifier,$payable,$apiBillPay['data']);
                $this->insertSenderCharges( $fixedCharge,$percent_charge, $total_charge, $amount,$user,$sender);
                //send sms notifications
                sendSms($user,'BILL_PAY',[
                    'amount'=> get_amount($amount,get_default_currency_code()),
                    'bill_type' => $bill_type??'',
                    'customer_identifier' =>$customer_identifier,
                    'trx' => $trx_id,
                    'time' =>  now()->format('Y-m-d h:i:s A'),
                    'balance' => get_amount($userWallet->balance,$userWallet->currency->code),
                ]);
                return redirect()->route("agent.bill.pay.index")->with(['success' => ['Bill Pay Request Send Successful']]);
            }else{
                return back()->with(['error' => [$apiBillPay['message']]]);
            }
        }catch(Exception $e) {
            return back()->with(['error' => [$e->getMessage()]]);
        }

    }
    public function insertSender( $trx_id,$agent,$agentWallet,$amount, $bill_type, $customer_identifier,$payable,$apiBillPay) {
        $trx_id = $trx_id;
        $authWallet = $agentWallet;
        $afterCharge = ($authWallet->balance - $payable);
        $details =[
            'bill_type_name' => $bill_type??'',
            'customer_identifier' => $customer_identifier,
            'bill_amount' => $amount??"",
            'flw_info' => $apiBillPay??[],
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                       => $agent->id,
                'agent_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::BILLPAY,
                'trx_id'                        => $trx_id,
                'request_amount'                => $amount,
                'payable'                       => $payable,
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::BILLPAY," ")) . " Request Sent",
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
                'title'         =>"Bill Pay ",
                'message'       => "Bill Pay request send " .$amount.' '.get_default_currency_code()." successful.",
                'image'         => files_asset_path('profile-default'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::BILL_PAY,
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
