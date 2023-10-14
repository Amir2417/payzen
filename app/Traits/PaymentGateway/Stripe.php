<?php

namespace App\Traits\PaymentGateway;

use Exception;
use Stripe\Token;
use Stripe\Charge;
use App\Models\StripeCard;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use App\Models\TemporaryData;
use Illuminate\Support\Carbon;
use App\Models\UserNotification;
use App\Http\Helpers\Api\Helpers;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\BasicSettings;
use Stripe\Stripe as StripePackage;
use App\Constants\NotificationConst;
use App\Models\Admin\PaymentGateway;
use App\Constants\PaymentGatewayConst;
use App\Http\Helpers\PaymentGatewayApi;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\PaymentGatewayCurrency;
use App\Providers\Admin\BasicSettingsProvider;
use App\Http\Controllers\User\AddMoneyController;
use App\Notifications\User\AddMoney\ApprovedMail;

trait Stripe
{
    public function stripeInit($output = null) {
        if(!$output) $output = $this->output;
        $gatewayAlias = $output['gateway']['alias'];
        $identifier = generate_unique_string("transactions","trx_id",16);
        $this->stripeJunkInsert($identifier);
        Session::put('identifier',$identifier);
        Session::put('output',$output);
        if(userGuard()['type'] == "AGENT"){
            return redirect()->route('agent.add.money.payment', $gatewayAlias);
        }elseif(userGuard()['type'] == "USER"){
            return redirect()->route('user.add.money.payment', $gatewayAlias);
        }
    }

    public function getStripeCredentials($output) {
        $gateway = $output['gateway'] ?? null;
        if(!$gateway) throw new Exception("Payment gateway not available");
        $client_id_sample = ['publishable_key','publishable key','publishable-key'];
        $client_secret_sample = ['secret id','secret-id','secret_id'];

        $client_id = '';
        $outer_break = false;
        foreach($client_id_sample as $item) {
            if($outer_break == true) {
                break;
            }
            $modify_item = $this->stripePlainText($item);
            foreach($gateway->credentials ?? [] as $gatewayInput) {
                $label = $gatewayInput->label ?? "";
                $label = $this->stripePlainText($label);

                if($label == $modify_item) {
                    $client_id = $gatewayInput->value ?? "";
                    $outer_break = true;
                    break;
                }
            }
        }


        $secret_id = '';
        $outer_break = false;
        foreach($client_secret_sample as $item) {
            if($outer_break == true) {
                break;
            }
            $modify_item = $this->stripePlainText($item);
            foreach($gateway->credentials ?? [] as $gatewayInput) {
                $label = $gatewayInput->label ?? "";
                $label = $this->stripePlainText($label);

                if($label == $modify_item) {
                    $secret_id = $gatewayInput->value ?? "";
                    $outer_break = true;
                    break;
                }
            }
        }

        return (object) [
            'publish_key'     => $client_id,
            'secret_key' => $secret_id,

        ];

    }

    public function stripePlainText($string) {
        $string = Str::lower($string);
        return preg_replace("/[^A-Za-z0-9]/","",$string);
    }

    public function stripeJunkInsert($response) {
        $output = $this->output;
        $data = [
            'gateway'   => $output['gateway']->id,
            'currency'  => $output['currency']->id,
            'amount'    => json_decode(json_encode($output['amount']),true),
            'response'  => $response,
        ];

        return TemporaryData::create([
            'type'          => PaymentGatewayConst::STRIPE,
            'identifier'    => $response,
            'data'          => $data,

        ]);
    }
    public function stripeJunkInserts($response,$output) {
       
        $output;
        $user = auth()->guard(get_auth_guard())->user();
        // dd($user);
        $creator_table = $creator_id = $wallet_table = $wallet_id = null;

        $creator_table = auth()->guard(get_auth_guard())->user()->getTable();
        $creator_id = auth()->guard(get_auth_guard())->user()->id;
        $wallet_table = $output['wallet']->getTable();
        $wallet_id = $output['wallet']->id;

        $data = [
            'gateway'      => $output['gateway']->id,
            'currency'     => $output['currency']->id,
            'sender_currency'     => $output['sender_currency']->id,
            'amount'       => json_decode(json_encode($output['amount']),true),
            'response'     => $response,
            'wallet_table'  => $wallet_table,
            'wallet_id'     => $wallet_id,
            'creator_table' => $creator_table,
            'creator_id'    => $creator_id,
            'creator_guard' => get_auth_guard(),
        ];
        return TemporaryData::create([
            'type'          => PaymentGatewayConst::STRIPE,
            'identifier'    => $response['tx_ref'],
            'data'          => $data,
        ]);
    }
    public function paymentConfirmed(Request $request){
        $basic_settings = BasicSettingsProvider::get();
        $stripe_card    = StripeCard::where('agent_id',auth()->user()->id)->where('id',$request->id)->first();
        // dd(decrypt($stripe_card->card_number));
        if(!$stripe_card) return back()->with(['error' => ['Please select a stripe card']]);
        $output = session()->get('output');
        // dd($output);
        $credentials = $this->getStripeCredentials($output);
        $reference = generateTransactionReference();
        $token = session()->get('identifier');
        $data = TemporaryData::where("identifier",$token)->first();
        if(!$data || $data == null){
            return back()->with(['error' => ["Invalid Request!"]]);
        }
        $amount = $output['amount']->total_amount ? number_format($output['amount']->total_amount,2,'.','') : 0;
        $currency = $output['currency']['currency_code']??"USD";
        if(auth()->guard(get_auth_guard())->check()){
            $user = auth()->guard(get_auth_guard())->user();
            $user_email = $user->email;
            $user_phone = $user->full_mobile ?? '';
            $user_name = $user->firstname.' '.$user->lastname ?? '';
        }
        $return_url = route('agent.add.money.stripe.payment.success', $reference);

         // Enter the details of the payment
         $data = [
            'payment_options' => 'card',
            'amount'          => $amount,
            'email'           => $user_email,
            'tx_ref'          => $reference,
            'currency'        =>  $currency,
            'redirect_url'    => $return_url,
            'customer'        => [
                'email'        => $user_email,
                "phone_number" => $user_phone,
                "name"         => $user_name
            ],
            "customizations" => [
                "title"       => "Add Money",
                "description" => dateFormat('d M Y', Carbon::now()),
            ]
        ];

       //start stripe pay link
       $stripe = new \Stripe\StripeClient($credentials->secret_key);

       //create product for Product Id
       try{
            $product_id = $stripe->products->create([
                'name' => 'Add Money( '.$basic_settings->site_name.' )',
            ]);
       }catch(Exception $e){
            throw new Exception($e->getMessage());
       }
       //create price for Price Id
       try{
            $price_id =$stripe->prices->create([
                'currency' =>  $currency,
                'unit_amount' => $amount*100,
                'product' => $product_id->id??""
              ]);
       }catch(Exception $e){
            throw new Exception("Something Is Wrong, Please Contact With Owner");
       }
       //create payment live links
       try{
            $payment_link = $stripe->paymentLinks->create([
                'line_items' => [
                [
                    'price' => $price_id->id,
                    'quantity' => 1,
                ],
                ],
                'after_completion' => [
                'type' => 'redirect',
                'redirect' => ['url' => $return_url],
                ],


            ]);
        }catch(Exception $e){
            throw new Exception("Something Is Wrong, Please Contact With Owner");
        }
        $this->stripeJunkInserts($data,$output);
            // "?prefilled_email=" . $user_email .
        $this->stripeJunkInserts($data,$output);
        $payment_url = $payment_link->url . "?prefilled_email=" . @$user->email .
        "&prefilled_cvc=" . "123" .
        "&prefilled_card_number=" . decrypt($stripe_card->card_number) .
        "&prefilled_card_expire=" . decrypt($stripe_card->expiration_date);
        return redirect($payment_url);

    }

    public function createTransactionStripe($output, $trx_id) {
        $trx_id =  $trx_id;
        $inserted_id = $this->insertRecordStripe($output,$trx_id);
        $this->insertChargesStripe($output,$inserted_id);
        $this->insertDeviceStripe($output,$inserted_id);
        $this->removeTempDataStripe($output);
    }

    public function insertRecordStripe($output, $trx_id) {

        $trx_id = $trx_id;
        $token = $this->output['tempData']['identifier'] ?? "";
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => auth()->user()->id,
                'user_wallet_id'                => $output['wallet']->id,
                'payment_gateway_currency_id'   => $output['currency']->id,
                'type'                          =>  "ADD-MONEY",
                'trx_id'                        => $trx_id,
                'request_amount'                => $output['amount']->requested_amount,
                'payable'                       => $output['amount']->total_amount,
                'available_balance'             => $output['wallet']->balance + $output['amount']->requested_amount,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPEADDMONEY," ")) . " With " . $output['gateway']->name,
                'details'                       => "strip payment successfull",
                'status'                        => true,
                'attribute'                      =>PaymentGatewayConst::SEND,
                'created_at'                    => now(),
            ]);

            $this->updateWalletBalanceStripe($output);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
        return $id;
    }

    public function updateWalletBalanceStripe($output) {
        $update_amount = $output['wallet']->balance + $output['amount']->requested_amount;
        $output['wallet']->update([
            'balance'   => $update_amount,
        ]);
    }

    public function insertChargesStripe($output,$id) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $output['amount']->percent_charge,
                'fixed_charge'      => $output['amount']->fixed_charge,
                'total_charge'      => $output['amount']->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         => "Add Money",
                'message'       => "Your Wallet (".$output['wallet']->currency->code.") balance  has been added ".$output['amount']->requested_amount.' '. $output['wallet']->currency->code,
                'time'          => Carbon::now()->diffForHumans(),
                'image'         => files_asset_path('profile-default'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::BALANCE_ADDED,
                'user_id'  =>  auth()->user()->id,
                'message'   => $notification_content,
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function insertDeviceStripe($output,$id) {
        $client_ip = request()->ip() ?? false;
        $location = geoip()->getLocation($client_ip);
        $agent = new Agent();

        // $mac = exec('getmac');
        // $mac = explode(" ",$mac);
        // $mac = array_shift($mac);
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

    public function removeTempDataStripe($output) {
        $token = session()->get('identifier');
        TemporaryData::where("identifier",$token)->delete();
    }
    //for api
    public function stripeInitApi($output = null) {
        if(!$output) $output = $this->output;
        $gatewayAlias = $output['gateway']['alias'];
        $identifier = generate_unique_string("transactions","trx_id",16);
        $this->stripeJunkInsert($identifier);
        $response=[
            'trx' => $identifier,
        ];
        return $response;
    }
    public function paymentConfirmedApi(Request $request){

         $validator = Validator::make($request->all(), [
            'track' => 'required',
            'name' => 'required',
            'cardNumber' => 'required',
            'cardExpiry' => 'required',
            'cardCVC' => 'required',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $track = $request->track;
        $data = TemporaryData::where('identifier',$track)->first();

        if(!$data){
            $error = ['error'=>["Sorry, your payment information is invalid"]];
            return Helpers::error($error);
        }
        $payment_gateway_currency = PaymentGatewayCurrency::where('id', $data->data->currency)->first();
        $gateway_request = ['currency' => $payment_gateway_currency->alias, 'amount'    => $data->data->amount->requested_amount];
        $output = PaymentGatewayApi::init($gateway_request)->gateway()->get();
        $credentials = $this->getStripeCredetials($output);

         $cc = $request->cardNumber;
         $exp = $request->cardExpiry;
         $cvc = $request->cardCVC;

         $exp = explode("/", $request->cardExpiry);
         $emo = trim($exp[0]);
         $eyr = trim($exp[1]);
         $cnts = round($data->data->amount->total_amount, 2) * 100;

         StripePackage::setApiKey(@$credentials->secret_key);
         StripePackage::setApiVersion("2020-03-02");

         try {
             $token = Token::create(array(
                     "card" => array(
                     "number" => "$cc",
                     "exp_month" => $emo,
                     "exp_year" => $eyr,
                     "cvc" => "$cvc"
                 )
             ));
             try {
                 $charge = Charge::create(array(
                     'card' => $token['id'],
                     'currency' => $data->data->amount->sender_cur_code,
                     'amount' => $cnts,
                     'description' => 'item',
                 ));

                 if ($charge['status'] == 'succeeded') {
                     $trx_id = 'AM'.getTrxNum();
                     $this->createTransactionStripe($output,$trx_id);
                     $user = auth()->user();
                     $user->notify(new ApprovedMail($user,$output,$trx_id));
                     $data->delete();
                     $message =  ['success'=>['Add Money Successfull']];
                     return Helpers::onlysuccess( $message);
                 }
             } catch (\Exception $e) {
                $error = ['error'=>[$e->getMessage()]];
                return Helpers::error($error);

             }
         } catch (\Exception $e) {
            $error = ['error'=>[$e->getMessage()]];
            return Helpers::error($error);
         }


     }

}
