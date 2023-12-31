<?php
namespace App\Http\Helpers;

use Exception;
use App\Models\UserWallet;
use App\Models\AgentWallet;
use Illuminate\Support\Str;
use App\Models\Admin\Currency;
use App\Http\Helpers\Api\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\PaymentGateway\Manual;
use App\Traits\PaymentGateway\Paypal;
use App\Traits\PaymentGateway\Stripe;
use App\Constants\PaymentGatewayConst;
use App\Traits\PaymentGateway\RazorTrait;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\PaymentGatewayCurrency;
use App\Traits\PaymentGateway\PagaditoTrait;
use Illuminate\Validation\ValidationException;
use App\Traits\PaymentGateway\FlutterwaveTrait;

class PaymentGatewayApi {

    use Paypal,Stripe,Manual,FlutterwaveTrait,RazorTrait,PagaditoTrait;

    protected $request_data;
    protected $output;
    protected $currency_input_name = "currency";
    protected $amount_input = "amount";
    protected $sender_wallet_name = "sender_wallet";

    public function __construct(array $request_data)
    {
        $this->request_data = $request_data;
    }

    public static function init(array $data) {
        return new PaymentGatewayApi($data);
    }

    public function gateway() {
        $request_data = $this->request_data;
        
        if(empty($request_data)){
            $error = ['error'=>['Gateway Information is not available. Please provide payment gateway currency alias']];
            return Helpers::error($error);
        }
        $validated = $this->validator($request_data)->validate();
        $gateway_currency = PaymentGatewayCurrency::where("alias",$validated[$this->currency_input_name])->first();

        if(!$gateway_currency || !$gateway_currency->gateway) {

            $error = ['error'=>['Gateway not available']];
            return Helpers::error($error);
        }
        // $user_wallet = UserWallet::auth()->where('id', $validated['sender_wallet'])->first();

        // $defualt_currency = Currency::where('id',$user_wallet->currency_id)->first();
        // dd($defualt_currency->id,$user_wallet->id);
           
        
        if(request()->expectsJson()){

            if(userGuard()['type'] == "AGENT"){
                $user_wallet = AgentWallet::auth()->where('id', $validated['sender_wallet'])->first();
                $defualt_currency = Currency::where('id',$user_wallet->currency_id)->first();
            }else{
                $user_wallet = UserWallet::auth()->where('id', $validated['sender_wallet'])->first();
                $defualt_currency = Currency::where('id',$user_wallet->currency_id)->first();
            }

       }else{
        if(auth()->guard('agent')->user()){
            $user = auth()->guard('agent')->user();

            $user_wallet = AgentWallet::where('agent_id',$user->id)->where('id', $validated['sender_wallet'])->first();
            $defualt_currency = Currency::where('id',$user_wallet->currency_id)->first();
            }else{
                $user_wallet = UserWallet::auth()->where('id', $validated['sender_wallet'])->first();
                $defualt_currency = Currency::where('id',$user_wallet->currency_id)->first();
            }
       }
       
        if(!$user_wallet) {
            $this->currency_input_name = "User wallet not found!";
            $error = ['error'=>['User wallet not found!']];
            return Helpers::error($error);
        }

        
        if($gateway_currency->gateway->isAutomatic()) {
            $this->output['gateway']    = $gateway_currency->gateway;
            $this->output['currency']   = $gateway_currency;
            $this->output['sender_currency']   = $defualt_currency;
            $this->output['wallet']     = $user_wallet;
            $this->output['amount']     = $this->amount();
            $this->output['distribute'] = $this->gatewayDistribute($gateway_currency->gateway);
        }elseif($gateway_currency->gateway->isManual()){
            $this->output['gateway']    = $gateway_currency->gateway;
            $this->output['currency']   = $gateway_currency;
            $this->output['sender_currency']   = $defualt_currency;
            $this->output['wallet']     = $user_wallet;
            $this->output['amount']     = $this->amount();
            $this->output['distribute'] = $this->gatewayDistribute($gateway_currency->gateway);

        }

        // limit validation
        $this->limitValidation($this->output);
        
        return $this;
    }

    public function validator($data) {
        return Validator::make($data,[
            $this->currency_input_name  => "required|exists:payment_gateway_currencies,alias",
            $this->amount_input         => "required|numeric",
            $this->sender_wallet_name   => "required",
        ]);

    }

    public function limitValidation($output) {

        $gateway_currency = $output['currency'];
        
        $requested_amount = $output['amount']->requested_amount;
        
        $exchange_rate  = $output['amount']->sender_cur_rate / $output['amount']->wallet_currency_rate;
        $amount = $requested_amount   * $exchange_rate;
        $min_amount = $gateway_currency->min_limit / $output['amount']->wallet_currency_rate;
        $max_amount = $gateway_currency->max_limit / $output['amount']->wallet_currency_rate;

       
        if($amount < $min_amount || $amount > $max_amount) {
            throw ValidationException::withMessages([
                $this->amount_input = "Please follow the transaction limit",
            ]);
        }
    }

    public function get() {
        return $this->output;
    }

    public function gatewayDistribute($gateway = null) {

        if(!$gateway) $gateway = $this->output['gateway'];
        $alias = Str::lower($gateway->alias);
        if($gateway->type == PaymentGatewayConst::AUTOMATIC){
            $method = PaymentGatewayConst::register($alias);
           
        }elseif($gateway->type == PaymentGatewayConst::MANUAL){
            
            $method = PaymentGatewayConst::register(strtolower($gateway->type));
        }
       
        if(method_exists($this,$method)) {
           
            return $method;
        }
        
        $error = ['error'=>["Gateway(".$gateway->name.") Trait or Method (".$method."()) does not exists"]];
        return Helpers::error($error);
    }

    public function amount() {
        $currency = $this->output['currency'] ?? null;
        
        $sender_currency = $this->output['sender_currency'] ?? null;
        if(!$currency) {
            $error = ['error'=>['Gateway currency not found']];
            return Helpers::error($error);
        }

        return $this->chargeCalculate($currency,$sender_currency,null);
    }

    public function chargeCalculate($currency,$sender_currency,$receiver_currency = null) {
        $amount = $this->request_data[$this->amount_input];
        
        $exchange   = $currency->rate / $sender_currency->rate;
        $request_amount     = $amount * $exchange;
        
        $sender_currency_rate = $currency->rate;
        ($sender_currency_rate == "" || $sender_currency_rate == null) ? $sender_currency_rate = 0 : $sender_currency_rate;
        ($request_amount == "" || $request_amount == null) ? $request_amount : $request_amount;

        if($currency != null) {
            $fixed_charges = $currency->fixed_charge;
            $percent_charges = $currency->percent_charge;
        }else {
            $fixed_charges = 0;
            $percent_charges = 0;
        }
        
        $fixed_charge_calc = $fixed_charges;
        $percent_charge_calc = (($amount / 100 ) * $percent_charges );

        $total_charge = $fixed_charge_calc + $percent_charge_calc;
       
        if($receiver_currency) {
            $receiver_currency_rate = $receiver_currency->rate;
            ($receiver_currency_rate == "" || $receiver_currency_rate == null) ? $receiver_currency_rate = 0 : $receiver_currency_rate;
            $exchange_rate = ($receiver_currency_rate / $sender_currency_rate);
            $will_get = $amount;

            $data = [
                'requested_amount'          => $amount,
                'sender_cur_code'           => $currency->currency_code,
                'sender_cur_rate'           => $sender_currency_rate ?? 0,
                'receiver_cur_code'         => $receiver_currency->currency_code,
                'receiver_cur_rate'         => $receiver_currency->rate ?? 0,
                'fixed_charge'              => $fixed_charge_calc,
                'percent_charge'            => $percent_charge_calc,
                'total_charge'              => $total_charge,
                'total_amount'              => $request_amount + $total_charge,
                'exchange_rate'             => $exchange_rate,
                'will_get'                  => $will_get,
                'wallet_currency'           => $sender_currency->code,
                'wallet_currency_rate'      => $sender_currency->rate,
            ];

        }else {
          
            $defualt_currency = Currency::default();
            $exchange_rate =  $defualt_currency->rate;
            $will_get = $amount;
            $total_Amount = $request_amount + $total_charge;
            
            $data = [
                'requested_amount'          => $amount,
                'sender_cur_code'           => $currency->currency_code,
                'sender_cur_rate'           => $sender_currency_rate ?? 0,
                'fixed_charge'              => $fixed_charge_calc,
                'percent_charge'            => $percent_charge_calc,
                'total_charge'              => $total_charge,
                'total_amount'              => $total_Amount,
                'exchange_rate'             => $exchange_rate,
                'will_get'                  => $will_get,
                'wallet_currency'           => $sender_currency->code,
                'wallet_currency_rate'      => $sender_currency->rate,
            ];
           
        }
        
        return (object) $data;
    }

    public function render() {
        $output = $this->output;

        if(!is_array($output)){
            $error = ['error'=>['Render Faild! Please call with valid gateway/credentials']];
            return Helpers::error($error);
        }

        $common_keys = ['gateway','currency','amount','distribute'];
        foreach($output as $key => $item) {
            if(!array_key_exists($key,$common_keys)) {
                $this->gateway();
                break;
            }
        }

        $distributeMethod = $this->output['distribute'];
        return $this->$distributeMethod($output);
    }

    public function responseReceive($type = null) {
        $tempData = $this->request_data;

        if(empty($tempData) || empty($tempData['type'])){
            $error = ['error'=>['Transaction faild. Record didn\'t saved properly. Please try again.']];
            return Helpers::error($error);
        }
        if($this->requestIsApiUser()) {
            $creator_table = $tempData['data']->creator_table ?? null;
            $creator_id = $tempData['data']->creator_id ?? null;
            $creator_guard = $tempData['data']->creator_guard ?? null;
            $api_authenticated_guards = PaymentGatewayConst::apiAuthenticateGuard();
            if($creator_table != null && $creator_id != null && $creator_guard != null) {
                if(!array_key_exists($creator_guard,$api_authenticated_guards)) throw new Exception('Request user doesn\'t save properly. Please try again');
                $creator = DB::table($creator_table)->where("id",$creator_id)->first();
                if(!$creator) throw new Exception("Request user doesn\'t save properly. Please try again");
                $api_user_login_guard = $api_authenticated_guards[$creator_guard];
                $this->output['api_login_guard'] = $api_user_login_guard;
                Auth::guard($api_user_login_guard)->loginUsingId($creator->id);
            }
        }

        $method_name = $tempData['type']."Success";
        

        $currency_id = $tempData['data']->currency ?? "";
        $gateway_currency = PaymentGatewayCurrency::find($currency_id);
        if(!$gateway_currency){
            $error = ['error'=>['Transaction faild. Gateway currency not available.']];
            return Helpers::error($error);
        }
        $requested_amount = $tempData['data']->amount->requested_amount ?? 0;
        $validator_data = [
            $this->currency_input_name => $gateway_currency->alias,
            $this->amount_input        => $requested_amount,
            $this->sender_wallet_name   => $tempData['data']->wallet_id,
        ];
        $this->request_data = $validator_data;
        $this->gateway();
        $this->output['tempData'] = $tempData;
        $type = $tempData['type'];
        if($type == 'flutterwave'){
            if(method_exists(FlutterwaveTrait::class,$method_name)) {
                return $this->$method_name($this->output);
            }
        }elseif($type == 'razorpay'){
            if(method_exists(RazorTrait::class,$method_name)) {
                return $this->$method_name($this->output);
            }
        }elseif($type == 'stripe'){
           
            if(method_exists(Stripe::class,$method_name)) {
                return $this->$method_name($this->output);
            }
        }
        elseif($type == 'pagadito'){
            if(method_exists(PagaditoTrait::class,$method_name)) {
                return $this->$method_name($this->output);
            }
        }else{
            if(method_exists(Paypal::class,$method_name)) {
                return $this->$method_name($this->output);
            }
        }
       
        $error = ['error'=>["Response method ".$method_name."() does not exists."]];
        return Helpers::error($error);

    }

    public function type($type) {
        $this->output['type']  = $type;
        return $this;
    }
    public function api() {
        $output = $this->output;
        $output['distribute']   = $this->gatewayDistribute() . "Api";
        // $this->output = $output;
        $method = $output['distribute'];
        $response = $this->$method($output);
        $output['response'] = $response;
        if( $output['distribute'] == "pagaditoInitApi"){
            $parts = parse_url( $output['response']);
                parse_str($parts['query'], $query);
                // Extract the token value
                if (isset($query['token'])) {
                    $tokenValue = $query['token'];
                } else {
                    $tokenValue = '';
                }
            $output['response'] =  $tokenValue;
        }


        $this->output = $output;
        return $this;
    }
    public function requestIsApiUser() {
        $request_source = request()->get('r-source');
        if($request_source != null && $request_source == PaymentGatewayConst::APP) return true;
        return false;
    }

}
