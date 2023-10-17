<?php

namespace App\Http\Controllers\Agent;

use Exception;
use App\Models\AgentWallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Constants\GlobalConst;
use App\Models\Admin\Currency;
use App\Models\BillPayCategory;
use App\Models\AgentNotification;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\BasicSettings;
use App\Constants\NotificationConst;
use App\Http\Controllers\Controller;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\AdminNotification;
use App\Models\Admin\TransactionSetting;
use Illuminate\Support\Facades\Validator;
use App\Providers\Admin\BasicSettingsProvider;
use App\Notifications\User\BillPay\BillPayMail;
use App\Events\Agent\NotificationEvent as UserNotificationEvent;

class BillPayController extends Controller
{
    protected $basic_settings;

    public function __construct()
    {
        $this->basic_settings = BasicSettingsProvider::get();
    }
    public function index() {
        $page_title = "Bill Pay";
        $sender_wallets = AgentWallet::auth()->whereHas('currency',function($q) {
            $q->where("sender",GlobalConst::ACTIVE)->where("status",GlobalConst::ACTIVE);
        })->active()->get();
        $charges = TransactionSetting::where('slug','bill_pay')->where('status',1)->first();
        $bill_types = BillPayCategory::active()->get();
        $transactions = Transaction::where('agent_id',auth()->user()->id)->billPay()->latest()->take(10)->get();
        return view('agent.sections.bill-pay.index',compact('page_title','sender_wallets','charges','transactions','bill_types'));
    }
    public function payConfirm(Request $request){
        $validated = Validator::make($request->all(),[
            'sender_amount'     => "required|numeric|gt:0",
            'sender_currency'   => "required|string|exists:currencies,code",
            'bill_type'         => "required|string|max:300",
            'bill_number'         => "required",
        ])->validate();
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

        $sender_wallet = AgentWallet::where('agent_id',auth()->user()->id)->whereHas("currency",function($q) use ($validated) {
            $q->where("code",$validated['sender_currency'])->active();
        })->active()->first();
        if(!$sender_wallet) return back()->with(['error' => ['Your wallet isn\'t available with currency ('.$validated['sender_currency'].')']]);
        $trx_charges = TransactionSetting::where('slug','bill_pay')->where('status',1)->first();;
        $charges = $this->billPayCharge($validated['sender_amount'],$trx_charges,$sender_wallet);

        $bill_type = BillPayCategory::where('slug', $validated['bill_type'])->first();
        if(!$bill_type) return back()->with(['error' => ['Your selected bill type  isn\'t available']]);
         // Check transaction limit
         $sender_currency_rate = $sender_wallet->currency->rate;
         $min_amount = $trx_charges->min_limit * $sender_currency_rate;
         $max_amount = $trx_charges->max_limit * $sender_currency_rate;
         if($charges['sender_amount'] < $min_amount || $charges['sender_amount'] > $max_amount) {
             return back()->with(['error' => ['Please follow the transaction limit. (Min '.$min_amount . ' ' . $sender_wallet->currency->code .' - Max '.$max_amount. ' ' . $sender_wallet->currency->code . ')']]);
         }
         if($charges['payable'] > $sender_wallet->balance) return back()->with(['error' => ['Your wallet balance is insufficient']]);
        try{
            $trx_id = 'BP'.getTrxNum();
            $sender = $this->insertSender($trx_id,$sender_wallet, $charges, $bill_type,$validated['bill_number']);
           
            $this->insertSenderCharges($sender,$charges,$sender_wallet);
            if( $this->basic_settings->email_notification == true){
                $notifyData = [
                    'trx_id'  => $trx_id,
                    'bill_type'  => @$bill_type->name,
                    'bill_number'  => @$validated['bill_number'],
                    'request_amount'   =>getAmount($charges['sender_amount'],2).' '.$charges['sender_currency'],
                    'charges'   => getAmount($charges['total_charge'],2).' '.$charges['sender_currency'],
                    'payable'  => getAmount($charges['payable'],2).' '.$charges['sender_currency'],
                    'current_balance'  => getAmount($sender_wallet->balance, 4).' '.$charges['sender_currency'],
                    'status'  => "Pending",
                ];
                //send notifications
                $user = auth()->user();
                $user->notify(new BillPayMail($user,(object)$notifyData));
            }
            return redirect()->route("agent.bill.pay.index")->with(['success' => ['Bill pay request send to admin successful']]);
        }catch(Exception $e) {
            return back()->with(['error' => [$e->getMessage()]]);
        }

    }
    public function insertSender( $trx_id,$sender_wallet, $charges, $bill_type,$bill_number) {
        $trx_id = $trx_id;
        $authWallet = $sender_wallet;
        $afterCharge = ($authWallet->balance -  $charges['payable']);
        $details =[
            'bill_type_id' => $bill_type->id??'',
            'bill_type_name' => $bill_type->name??'',
            'bill_number' => $bill_number,
            'bill_amount' => $charges['sender_amount']??"",
            'charges' => $charges,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                       => $authWallet->agent_id,
                'agent_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::BILLPAY,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['sender_amount'],
                'payable'                       => $charges['payable'],
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::BILLPAY," ")) . " Request To Admin",
                'details'                       => json_encode($details),
                'attribute'                     =>PaymentGatewayConst::SEND,
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
    public function insertSenderCharges($id,$charges,$sender_wallet) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    =>  $id,
                'percent_charge'    =>  $charges['percent_charge'],
                'fixed_charge'      =>  $charges['fixed_charge'],
                'total_charge'      =>  $charges['total_charge'],
                'created_at'        =>  now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>"Bill Pay ",
                'message'       => "Bill Pay Request Send To Admin " .$charges['sender_amount'].' '.$charges['sender_currency']." Successful.",
                'image'         => get_image($sender_wallet->agent->image,'user-profile'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::BILL_PAY,
                'agent_id'  => $sender_wallet->agent->id,
                'message'   => $notification_content,
            ]);
            //Push Notifications
            event(new UserNotificationEvent($notification_content,$sender_wallet->agent));
            send_push_notification(["user-".$sender_wallet->agent->id],[
                'title'     => $notification_content['title'],
                'body'      => $notification_content['message'],
                'icon'      => $notification_content['image'],
            ]);

           //admin notification
           $notification_content['title'] = "Bill Pay Request Send To Admin  ".$charges['sender_amount'].' '.$charges['sender_currency'].' Successful ('.$sender_wallet->agent->username.')';
           AdminNotification::create([
               'type'      => NotificationConst::BILL_PAY,
               'admin_id'  => 1,
               'message'   => $notification_content,
           ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
    public function billPayCharge($sender_amount,$charges,$sender_wallet) {
        $data['sender_amount']          = $sender_amount;
        $data['sender_currency']        = $sender_wallet->currency->code;
        $data['sender_currency_rate']   = $sender_wallet->currency->rate;
        $data['percent_charge']         = ($sender_amount / 100) * $charges->percent_charge ?? 0;
        $data['fixed_charge']           = $sender_wallet->currency->rate * $charges->fixed_charge ?? 0;
        $data['total_charge']           = $data['percent_charge'] + $data['fixed_charge'];
        $data['sender_wallet_balance']  = $sender_wallet->balance;
        $data['payable']                = $sender_amount + $data['total_charge'];
        return $data;
    }
}
