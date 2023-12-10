<?php

namespace App\Http\Controllers\Agent;

use Exception;
use App\Models\AgentWallet;
use App\Models\Transaction;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use App\Models\Admin\Currency;
use Illuminate\Support\Carbon;
use App\Models\UserNotification;
use App\Models\AgentNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\BasicSettings;
use App\Constants\NotificationConst;
use App\Http\Controllers\Controller;
use App\Models\Admin\PaymentGateway;
use Illuminate\Support\Facades\Auth;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\AdminNotification;
use App\Traits\ControlDynamicInputFields;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\PaymentGatewayCurrency;
use App\Notifications\User\Withdraw\WithdrawMail;
use App\Events\Agent\NotificationEvent as AgentNotificationEvent;

class MoneyOutController extends Controller
{
    use ControlDynamicInputFields;
    public function index(){
        $page_title         = "Withdraw Money";
        $currencies         = Currency::active()->get();
        $payment_gateways   = PaymentGatewayCurrency::whereHas('gateway', function ($gateway) {
            $gateway->where('slug', PaymentGatewayConst::money_out_slug());
            $gateway->where('status', 1);
        })->get();
        $transactions       = Transaction::where('agent_id',auth()->user()->id)->moneyOut()->orderByDesc("id")->latest()->take(10)->get();
       
        return view('agent.sections.withdraw.index',compact(    'page_title','payment_gateways','transactions','currencies'
        ));
    }

    public function paymentInsert(Request $request){
        $request->validate([
            'gateway'           => "required|exists:payment_gateway_currencies,alias",
            'amount'            => "required|numeric|gt:0",
            'wallet_currency'   => "required|exists:currencies,code",
        ]);
        $amount = $request->amount;
        $wallet_currency = $request->wallet_currency;
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


        $userWallet = AgentWallet::auth()->whereHas("currency",function($q) use ($wallet_currency) {
            $q->where("code",$wallet_currency)->active();
        })->active()->first();

        $gate =PaymentGatewayCurrency::whereHas('gateway', function ($gateway) {
            $gateway->where('slug', PaymentGatewayConst::money_out_slug());
            $gateway->where('status', 1);
        })->where('alias',$request->gateway)->first();

        if (!$gate) {
            return back()->with(['error' => ['Invalid Gateway']]);
        }

        if($amount < (($gate->min_limit/$gate->rate) * $userWallet->currency->rate) || $amount > (($gate->max_limit/$gate->rate) * $userWallet->currency->rate)) {
            return back()->with(['error' => ['Please follow the transaction limit']]);
        }
        $charges = $this->chargeCalculate( $gate,$userWallet,$amount);

        if( $charges->payable > $userWallet->balance){

            return back()->with(['error' => ['Insufficient Balance']]);
        }
        $data['user_id']= $user->id;
        $data['gateway_name']= $gate->gateway->name;
        $data['gateway_type']= $gate->gateway->type;
        $data['wallet_id']= $userWallet->id;
        $data['trx_id']= 'MO'.getTrxNum();
        $data['amount'] =  $amount;
        $data['gateway_id'] = $gate->gateway->id;
        $data['gateway_currency_id'] = $gate->id;
        $data['gateway_currency'] = strtoupper($gate->currency_code);
        $data['charges'] = $charges;

        session()->put('moneyoutData', $data);
        return redirect()->route('agent.withdraw.preview');
   }
   public function chargeCalculate($currency,$receiver_currency,$amount) {

    $amount = $amount;
    $sender_currency_rate = $currency->rate;
    ($sender_currency_rate == "" || $sender_currency_rate == null) ? $sender_currency_rate = 0 : $sender_currency_rate;
    ($amount == "" || $amount == null) ? $amount : $amount;

    if($currency != null) {
        $fixed_charges = $currency->fixed_charge;
        $percent_charges = $currency->percent_charge;
    }else {
        $fixed_charges = 0;
        $percent_charges = 0;
    }

    $fixed_charge_calc =  $fixed_charges;
    $percent_charge_calc = ($amount / 100 ) * $percent_charges;

    $total_charge = $fixed_charge_calc + $percent_charge_calc;

    $receiver_currency = $receiver_currency->currency;
    $receiver_currency_rate = $receiver_currency->rate;
    ($receiver_currency_rate == "" || $receiver_currency_rate == null) ? $receiver_currency_rate = 0 : $receiver_currency_rate;
    $exchange_rate = ($sender_currency_rate / $receiver_currency_rate);
    $conversion_amount =  $amount * $exchange_rate;
    $will_get = $conversion_amount;
    $payable =  $amount + $total_charge;

    $data = [
        'requested_amount'          => $amount,
        'gateway_cur_code'          => $currency->currency_code,
        'gateway_cur_rate'          => $sender_currency_rate ?? 0,
        'wallet_cur_code'           => $receiver_currency->code,
        'wallet_cur_rate'           => $receiver_currency->rate ?? 0,
        'fixed_charge'              => $fixed_charge_calc,
        'percent_charge'            => $percent_charge_calc,
        'total_charge'              => $total_charge,
        'conversion_amount'         => $conversion_amount,
        'payable'                   => $payable,
        'exchange_rate'             => $exchange_rate,
        'will_get'                  => $will_get,
        'default_currency'          => get_default_currency_code(),
    ];
    return (object) $data;
}
   public function preview(){
    $moneyOutData = (object)session()->get('moneyoutData');
    $moneyOutDataExist = session()->get('moneyoutData');
    if($moneyOutDataExist  == null){
        return redirect()->route('agent.withdraw.index');
    }
    $gateway = PaymentGateway::where('id', $moneyOutData->gateway_id)->first();
    if($gateway->type == "AUTOMATIC"){
        $page_title = "Withdraw Via ".$gateway->name;
        if(strtolower($gateway->name) == "flutterwave"){
            $credentials = $gateway->credentials;
            $data = null;
            foreach ($credentials as $object) {
                $object = (object)$object;
                if ($object->label === "Secret key") {
                    $data = $object;
                    break;
                }
            }
            $countries = get_all_countries();
            $currency =  $moneyOutData->gateway_currency;
            $country = Collection::make($countries)->first(function ($item) use ($currency) {
                return $item->currency_code === $currency;
            });

            $allBanks = getFlutterwaveBanks($country->iso2);
            return view('agent.sections.withdraw.automatic.'.strtolower($gateway->name),compact('page_title','gateway','moneyOutData','allBanks'));
        }else{
            return back()->with(['error' => ["Something is wrong, please try again later"]]);
        }
    }else{
        $page_title = "Withdraw Via ".$gateway->name;
        return view('agent.sections.withdraw.preview',compact('page_title','gateway','moneyOutData'));

    }

   }
   public function confirmMoneyOut(Request $request){
    $moneyOutData = (object)session()->get('moneyoutData');
    $gateway = PaymentGateway::where('id', $moneyOutData->gateway_id)->first();
    $payment_fields = $gateway->input_fields ?? [];

    $validation_rules = $this->generateValidationRules($payment_fields);
    $payment_field_validate = Validator::make($request->all(),$validation_rules)->validate();
    $get_values = $this->placeValueWithFields($payment_fields,$payment_field_validate);
        try{
            $inserted_id = $this->insertRecordManual($moneyOutData,$gateway,$get_values);
            $this->insertChargesManual($moneyOutData,$inserted_id);
            $this->insertDeviceManual($moneyOutData,$inserted_id);
            //sms notification
            sendSms(auth()->user(),'WITHDRAW_REQUEST',[
                'amount'=> get_amount($moneyOutData->amount,get_default_currency_code()),
                'method_name' => $moneyOutData->gateway_name,
                'trx' => $moneyOutData->trx_id,
                'time' =>  now()->format('Y-m-d h:i:s A'),
                'will_get' => get_amount($moneyOutData->will_get,$moneyOutData->gateway_currency),
                'currency' => $moneyOutData->gateway_currency,
            ]);
            session()->forget('moneyoutData');
            return redirect()->route("agent.withdraw.index")->with(['success' => ['Withdraw money request send to admin successful']]);
        }catch(Exception $e) {
            return back()->with(['error' => [$e->getMessage()]]);
        }

   }
   public function confirmMoneyOutAutomatic(Request $request){
    $basic_setting = BasicSettings::first();
    if($request->gateway_name == 'flutterwave'){
        $request->validate([
            'bank_name' => 'required|numeric|gt:0',
            'account_number' => 'required'
        ]);
        $moneyOutData = (object)session()->get('moneyoutData');
        $gateway = PaymentGateway::where('id', $moneyOutData->gateway_id)->first();

        $credentials = $gateway->credentials;
        $data = null;
        $secret_key = getPaymentCredentials($credentials,'Secret key');
        $base_url = getPaymentCredentials($credentials,'Base Url');
        $callback_url = getPaymentCredentials($credentials,'Callback Url');
        $ch = curl_init();
        $url =  $base_url.'/transfers';
        $data = [
            "account_bank" => $request->bank_name,
            "account_number" => $request->account_number,
            "amount" => $moneyOutData->charges->will_get,
            "narration" => "Withdraw from wallet",
            "currency" =>$moneyOutData->gateway_currency,
            "reference" => generateTransactionReference(),
            "callback_url" => $callback_url,
            "debit_currency" => $moneyOutData->gateway_currency
        ];
        $headers = [
            'Authorization: Bearer '.$secret_key,
            'Content-Type: application/json'
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return back()->with(['error' => [curl_error($ch)]]);
        } else {
            $result = json_decode($response,true);
            if($result['status'] && $result['status'] == 'success'){
                try{
                    $get_values =[
                        'user_data' => null,
                        'charges' => $moneyOutData->charges,

                    ];
                    //send notifications
                    $user = auth()->user();
                    $inserted_id = $this->insertRecordManual($moneyOutData,$gateway,$get_values);
                    $this->insertChargesAutomatic($moneyOutData,$inserted_id);
                    $this->insertDeviceManual($moneyOutData,$inserted_id);
                    session()->forget('moneyoutData');
                    if( $basic_setting->email_notification == true){
                        $user->notify(new WithdrawMail($user,$moneyOutData));
                    }
                    return redirect()->route("agent.withdraw.index")->with(['success' => ['Withdraw money request send successful']]);
                }catch(Exception $e) {
                    return back()->with(['error' => [$e->getMessage()]]);
                }

            }else{
                return back()->with(['error' => [$result['message']]]);
            }
        }

        curl_close($ch);

    }else{
        return back()->with(['error' => ["Invalid request,please try again later"]]);
    }


   }


   //check flutterwave banks
   public function checkBanks(Request $request){
    $bank_account = $request->account_number;
    $bank_code = $request->bank_code;
    $exist['data'] = (checkBankAccount($secret_key = null,$bank_account,$bank_code));
    return response( $exist);
   }

    public function insertRecordManual($moneyOutData,$gateway,$get_values) {
    if($moneyOutData->gateway_type == "AUTOMATIC"){
        $status = 1;
    }else{
        $status = 2;
    }

    $trx_id = $moneyOutData->trx_id ??'MO'.getTrxNum();
    $authWallet = AgentWallet::where('id',$moneyOutData->wallet_id)->where('agent_id',$moneyOutData->user_id)->first();
    $afterCharge = ($authWallet->balance - ($moneyOutData->charges->payable));
    DB::beginTransaction();
    try{
        $id = DB::table("transactions")->insertGetId([
            'agent_id'                       => auth()->user()->id,
            'agent_wallet_id'                => $moneyOutData->wallet_id,
            'payment_gateway_currency_id'   => $moneyOutData->gateway_currency_id,
            'type'                          => PaymentGatewayConst::TYPEMONEYOUT,
            'trx_id'                        => $trx_id,
            'request_amount'                => $moneyOutData->charges->requested_amount,
            'payable'                       => $moneyOutData->charges->will_get,
            'available_balance'             => $afterCharge,
            'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPEMONEYOUT," ")) . " by " .$gateway->name,
            'details'                       => json_encode($get_values),
            'status'                        => $status,
            'created_at'                    => now(),
        ]);
        $this->updateWalletBalanceManual($authWallet,$afterCharge);

        DB::commit();
    }catch(Exception $e) {
        DB::rollBack();
        throw new Exception($e->getMessage());
    }
    return $id;
    }

    public function updateWalletBalanceManual($authWallet,$afterCharge) {
        $authWallet->update([
            'balance'   => $afterCharge,
        ]);
    }
    public function insertChargesAutomatic($moneyOutData,$id) {

        if(Auth::guard(get_auth_guard())->check()){
            $user = auth()->guard(get_auth_guard())->user();
        }
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $moneyOutData->charges->percent_charge,
                'fixed_charge'      => $moneyOutData->charges->fixed_charge,
                'total_charge'      => $moneyOutData->charges->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         => "Withdraw",
                'message'       => "Your Withdraw Request  " .$moneyOutData->amount.' '.$moneyOutData->charges->wallet_cur_code." Successful",
                'image'         => get_image($user->image,'user-profile'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::MONEY_OUT,
                'agent_id'  =>  auth()->user()->id,
                'message'   => $notification_content,
            ]);
            DB::commit();

             //Push Notifications
             event(new AgentNotificationEvent($notification_content,$user));
             send_push_notification(["user-".$user->id],[
                 'title'     => $notification_content['title'],
                 'body'      => $notification_content['message'],
                 'icon'      => $notification_content['image'],
             ]);

            //admin notification
            $notification_content['title'] = 'Withdraw Request '.$moneyOutData->amount.' '.$moneyOutData->charges->wallet_cur_code.' By '.$moneyOutData->gateway_name.' '.$moneyOutData->gateway_currency.' Successful ('.$user->username.')';
            AdminNotification::create([
                'type'      => NotificationConst::MONEY_OUT,
                'admin_id'  => 1,
                'message'   => $notification_content,
            ]);
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
    public function insertChargesManual($moneyOutData,$id) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $moneyOutData->gateway_percent_charge,
                'fixed_charge'      => $moneyOutData->gateway_fixed_charge,
                'total_charge'      => $moneyOutData->gateway_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         => "Money Out",
                'message'       => "Your Money Out request send to admin " .$moneyOutData->amount.' '.get_default_currency_code()." successful",
                'image'         => files_asset_path('profile-default'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::MONEY_OUT,
                'agent_id'  =>  $moneyOutData->agent_id,
                'message'   => $notification_content,
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function insertDeviceManual($output,$id) {
        $client_ip = request()->ip() ?? false;
        $location = geoip()->getLocation($client_ip);
        $agent = new Agent();
        $mac = "";

        DB::beginTransaction();
        try{
            DB::table("transaction_devices")->insert([
                'transaction_id'=> $id,
                'ip'            => $client_ip,
                'mac'           => $mac,
                'city'          => $location['city'] ?? "",
                'country'       => $location['country'] ?? "",
                'longitude'     => $location['lon'] ?? "",
                'latitude'      => $location['lat'] ?? "",
                'timezone'      => $location['timezone'] ?? "",
                'browser'       => $agent->browser() ?? "",
                'os'            => $agent->platform() ?? "",
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
}
