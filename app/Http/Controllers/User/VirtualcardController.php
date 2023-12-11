<?php

namespace App\Http\Controllers\User;

use App\Constants\GlobalConst;
use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Models\Admin\AdminNotification;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\Currency;
use App\Models\Admin\TransactionSetting;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Models\VirtualCard;
use App\Models\VirtualCardApi;
use App\Notifications\User\VirtualCard\CreateMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Events\User\NotificationEvent as UserNotificationEvent;

class VirtualcardController extends Controller

{
    protected $api;
    public function __construct()
    {
        $cardApi = VirtualCardApi::first();
        $this->api =  $cardApi;
    }
    public function index()
    {
        $page_title = "Virtual Card";
        $myCard = VirtualCard::where('user_id',auth()->user()->id)->first();
        $cardCharge = TransactionSetting::where('slug','virtual_card')->where('status',1)->first();
        $transactions = Transaction::auth()->virtualCard()->latest()->take(5)->get();
        $cardApi = $this->api;
        $sender_wallets = UserWallet::with(['currency'])->auth()->where('status',true)->get();
        return view('user.sections.virtual-card.index',compact('page_title','myCard','transactions','cardCharge','cardApi','sender_wallets'));
    }
    public function cardDetails($card_id)
    {
        $page_title = "Card Details";
        $myCard = VirtualCard::where('card_id',$card_id)->first();
        return view('user.sections.virtual-card.detaials',compact('page_title','myCard'));
    }

    public function cardBuy(Request $request)
    {
        $request->validate([
            'card_amount' => 'required|numeric|gt:0',
            'currency' => "nullable|string|exists:currencies,code",
        ]);

        $basic_setting = BasicSettings::first();
        $user = auth()->user();
        $wallet_currency = $request->currency;
        if($basic_setting->kyc_verification){
            if( $user->kyc_verified == 0){
                return redirect()->route('user.profile.index')->with(['error' => ['Please submit kyc information']]);
            }elseif($user->kyc_verified == 2){
                return redirect()->route('user.profile.index')->with(['error' => ['Please wait before admin approved your kyc information']]);
            }elseif($user->kyc_verified == 3){
                return redirect()->route('user.profile.index')->with(['error' => ['Admin rejected your kyc information, Please re-submit again']]);
            }
        }
        $amount = $request->card_amount;
        $wallet = UserWallet::auth()->whereHas("currency",function($q) use ($wallet_currency) {
            $q->where("code",$wallet_currency)->active();
        })->active()->first();

        if(!$wallet){
            return back()->with(['error' => ['Wallet not found']]);
        }

        $cardCharge = TransactionSetting::where('slug','virtual_card')->where('status',1)->first();
        $charges = $this->virtualCardCharge($request['card_amount'],$cardCharge,$wallet);

        $minLimit =  $cardCharge->min_limit *  get_default_currency_rate();
        $maxLimit =  $cardCharge->max_limit *  get_default_currency_rate();
        if($amount < $minLimit || $amount > $maxLimit) {
            return back()->with(['error' => ['Please follow the transaction limit. (Min '.$minLimit . ' ' . get_default_currency_code() .' - Max '.$maxLimit. ' ' . get_default_currency_code() . ')']]);
        }

        $payable = $charges->payable;
        if($payable > $wallet->balance ){
            return back()->with(['error' => ['Sorry, Insufficient Balance On '. $wallet->currency->currency_code.' Wallet' ]]);
        }

        $currency = get_default_currency_code();
        $tempId = 'tempId-'. $user->id . time() . rand(6, 100);
        $trx = 'VC-' . time() . rand(6, 100);

        $callBack = route('user.virtual.card.flutterWave.callBack').'?c_user_id='.$user->id.'&c_amount='.  $amount.'&c_temp_id='.$tempId.'&c_trx='.$trx;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->api->config->flutterwave_url.'/virtual-cards',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "{\n    \"currency\": \"$currency\",\n    \"amount\":  $amount,\n    \"billing_name\": \"$user->name\",\n   \"callback_url\": \"$callBack/\"\n}",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer " .$this->api->config->flutterwave_secret_key
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response, true);

        if (isset($result)){
            if ( $result['status'] === 'success' && array_key_exists('data', $result) ) {
                $values = $result['data'];
                $filteredCollection = array_filter($values, function ($item) use ($currency) {
                    return $item['currency'] === $currency;
                });


                if(empty($filteredCollection )){
                    return back()->with(['error' => ['Sorry, Currently Not Supported Create Virtual For '. $currency]]);
                }

                $values =  $filteredCollection;
                $k = array_rand($values);
                $result = (object) $values[$k];
                //Save Card
                $v_card = new VirtualCard();
                $v_card->user_id = $user->id;
                $v_card->card_id = $result->id;
                $v_card->name = $user->fullname;
                $v_card->account_id = $result->account_id;
                $v_card->card_hash = $result->card_hash;
                $v_card->card_pan = $result->card_pan;
                $v_card->masked_card = $result->masked_pan;
                $v_card->cvv = $result->cvv;
                $v_card->expiration = $result->expiration;
                $v_card->card_type = $result->card_type;
                $v_card->name_on_card = $result->name_on_card;
                $v_card->callback = $result->callback_url;
                $v_card->ref_id = $trx;
                $v_card->secret = $trx;
                $v_card->bg = "DeepBlue";
                $v_card->city = $result->city;
                $v_card->state = $result->state;
                $v_card->zip_code = $result->zip_code;
                $v_card->address = $result->address_1;
                $v_card->amount =  $amount;
                $v_card->currency = $currency;
                $v_card->charge =  $charges->total_charge;
                if ($result->is_active) {
                    $v_card->is_active = 1;
                } else {
                    $v_card->is_active = 0;
                }
                $v_card->funding = 1;
                $v_card->terminate = 0;
                $v_card->save();

              $trx_id =  'CB'.getTrxNum();
              $sender = $this->insertCadrBuy( $trx_id,$user,$wallet,$amount, $v_card ,$payable,$charges);
              $this->insertBuyCardCharge($sender,$charges,$user,$v_card->masked_card);
                return redirect()->route("user.virtual.card.index")->with(['success' => ['Card Successfully Buy']]);
                 //sender notifications
            if( $basic_setting->email_notification == true){
                $notifyDataSender = [
                    'trx_id'  => $trx_id,
                    'title'  => "Virtual Card (Buy Card)",
                    'request_amount'  => getAmount($amount,4).' '.$wallet->currency->code,
                    'payable'   =>  getAmount($charges->payable,4).' ' .$wallet->currency->code,
                    'charges'   => getAmount( $charges->total_charge, 4).' ' .$wallet->currency->code,
                    'card_amount'  => getAmount( $v_card->amount, 4).' ' .$wallet->currency->code,
                    'card_pan'  => $v_card->maskedPan,
                    'status'  => "Success",
                  ];
                $user->notify(new CreateMail($user,(object)$notifyDataSender));
            }
            }else {
                return redirect()->back()->with(['error' => [@$result['message']??'Please wait a moment & try again later.']]);
            }
        }

    }
    public function cardFundConfirm(Request $request){
        $request->validate([
            'id' => 'required|integer',
            'fund_amount' => 'required|numeric|gt:0',
            'wallet_currency' => "required|string|exists:currencies,code",
        ]);
        $basic_setting = BasicSettings::first();
        $user = auth()->user();
        $wallet_currency = $request->wallet_currency;
        if($basic_setting->kyc_verification){
            if( $user->kyc_verified == 0){
                return redirect()->route('user.profile.index')->with(['error' => ['Please submit kyc information']]);
            }elseif($user->kyc_verified == 2){
                return redirect()->route('user.profile.index')->with(['error' => ['Please wait before admin approved your kyc information']]);
            }elseif($user->kyc_verified == 3){
                return redirect()->route('user.profile.index')->with(['error' => ['Admin rejected your kyc information, Please re-submit again']]);
            }
        }

        $myCard =  VirtualCard::where('user_id',auth()->user()->id)->where('id',$request->id)->first();

        if(!$myCard){
            return back()->with(['error' => ['Your Card not found']]);
        }
        $amount = $request->fund_amount;
        $wallet = UserWallet::auth()->whereHas("currency",function($q) use ($wallet_currency) {
            $q->where("code",$wallet_currency)->active();
        })->active()->first();

        if(!$wallet){
            return back()->with(['error' => ['Wallet not found']]);
        }

        $cardCharge = TransactionSetting::where('slug','virtual_card')->where('status',1)->first();
        $charges = $this->virtualCardCharge($request['fund_amount'],$cardCharge,$wallet);


        $minLimit =  $cardCharge->min_limit *  get_default_currency_rate();
        $maxLimit =  $cardCharge->max_limit *  get_default_currency_rate();
        if($amount < $minLimit || $amount > $maxLimit) {
            return back()->with(['error' => ['Please follow the transaction limit. (Min '.$minLimit . ' ' . get_default_currency_code() .' - Max '.$maxLimit. ' ' . get_default_currency_code() . ')']]);
        }

        $payable = $charges->payable;
        if($payable > $wallet->balance ){
            return back()->with(['error' => ['Sorry, Insufficient Balance On '. $wallet->currency->currency_code.' Wallet' ]]);
        }

        $currency = get_default_currency_code();
        $tempId = 'tempId-'. $user->id . time() . rand(6, 100);
        $trx = 'VC-' . time() . rand(6, 100);
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL =>  $this->api->config->flutterwave_url."/"."virtual-cards/".$myCard->card_id."/fund",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS =>"{\n \"debit_currency\": \"$currency\",\n    \"amount\": $amount\n}",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "Authorization: Bearer " .$this->api->config->flutterwave_secret_key
        ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response);

        if(!empty($result->status)  && $result->status == "success"){
            //added fund amount to card
            $myCard->amount += $amount;
            $myCard->save();
            $trx_id = 'CF'.getTrxNum();
            $sender = $this->insertCardFund( $trx_id,$user,$wallet,$amount, $myCard ,$payable,$charges);
            $this->insertFundCardCharge( $sender,$charges,$user,$myCard->maskedPan);
            return redirect()->route("user.virtual.card.index")->with(['success' => ['Card fund successfully']]);

        }else{
            return redirect()->back()->with(['error' => [@$result->message??'Please wait a moment & try again later.']]);
        }

    }
    public function cardBlockUnBlock(Request $request) {
        $validator = Validator::make($request->all(),[
            'status'                    => 'required|boolean',
            'data_target'               => 'required|string',
        ]);
        if ($validator->stopOnFirstFailure()->fails()) {
            $error = ['error' => $validator->errors()];
            return Response::error($error,null,400);
        }
        $validated = $validator->safe()->all();
        if($request->status == 1 ){
            $card = VirtualCard::where('id',$request->data_target)->where('is_active',1)->first();
            $status = 'block';
            if(!$card){
                $error = ['error' => ['Something is wrong in your card']];
                return Response::error($error,null,404);
            }
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->api->config->flutterwave_url.'/'."virtual-cards/".$card->card_id."/status/".$status,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "PUT",
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Authorization: Bearer " .$this->api->config->flutterwave_secret_key
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $result = json_decode($response, true);
            if (isset($result)) {
                if ($result['status'] === 'success' && array_key_exists('data', $result)) {
                    $card->is_active = 0;
                    $card->save();
                    $success = ['success' => [' Card block successfully']];
                    return Response::success($success,null,200);
                } elseif ($result['status'] === 'error' && $result['message'] === 'Card has been blocked previously') {
                    $card->is_active = 0;
                    $card->save();
                    $error = ['error' => ['Card has been blocked previously']];
                    return Response::error($error, null, 404);
                } elseif ($result['status'] === 'error' && $result['message'] === 'Card not found. Please check and try again') {
                    $card->terminate = 1;
                    $card->save();
                    $error = ['error' => ['This Card has been terminated previously.']];
                    return Response::error($error, null, 404);
                } else {
                    $error = ['error' => [$result['message']]];
                    return Response::error($error, null, 404);
                }
            }


        }else{
            $card = VirtualCard::where('id',$request->data_target)->where('is_active',0)->first();
        $status = 'unblock';
        if(!$card){
            $error = ['error' => ['Something is wrong in your card']];
            return Response::error($error,null,404);
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL =>  $this->api->config->flutterwave_url.'/'."virtual-cards/".$card->card_id."/status/".$status,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer " . $this->api->config->flutterwave_secret_key
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($response, true);
        if (isset($result)) {
            if ( $result['status'] === 'success' && array_key_exists('data', $result)) {
                $card->is_active = 1;
                $card->save();
                $success = ['success' => [' Card unblock successfully']];
                return Response::success($success,null,200);
            } elseif ( $result['status'] === 'error' && $result['message'] === 'Card has been blocked previously' ) {
                $card->is_active = 1;
                $card->save();
                $error = ['error' => ['Card has been blocked previously']];
                return Response::error($error, null, 404);
            }elseif ( $result['status'] === 'error' && $result['message'] === 'Card not found. Please check and try again' ) {
                $card->terminate = 1;
                $card->save();
                $error = ['error' => ['This Card has been terminated previously.']];
                return Response::error($error, null, 404);
            }else{
                $error = ['error' => [$result['message']]];
                return Response::error($error, null, 404);
            }
        }
        }
    }
    public function cardTransaction($card_id) {
        $user = auth()->user();
        $card = VirtualCard::where('user_id',$user->id)->where('card_id', $card_id)->first();
        $page_title = "Virtual Card Transaction ";
        $id = $card->card_id;
        $emptyMessage  = 'No Transaction Found!';
        $start_date = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-12 month" ) );
        $end_date = date('Y-m-d');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL =>  $this->api->config->flutterwave_url."/"."virtual-cards/".$id."/transactions?from=".date('Y-m-d',strtotime($start_date))."&to=".$end_date."&index=0&size=2147483647",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer " . $this->api->config->flutterwave_secret_key
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $card_truns = json_decode($response,true);


        return view('user.sections.virtual-card.trx',compact('page_title','card','card_truns'));


    }

    //card buy helper
    public function insertCadrBuy( $trx_id,$user,$wallet,$amount, $v_card ,$payable,$charges) {
        $trx_id = $trx_id;
        $authWallet = $wallet;
        $afterCharge = ($authWallet->balance - $payable);
        $details =[
            'card_info' =>   $v_card??'',
            'charges' =>   $charges,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $user->id,
                'user_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::VIRTUALCARD,
                'trx_id'                        => $trx_id,
                'request_amount'                => $amount,
                'payable'                       => $payable,
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::CARDBUY," ")),
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::RECEIVED,
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
    public function insertBuyCardCharge($id,$charges,$user,$masked_card) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    =>  $charges->percent_charge,
                'fixed_charge'      =>  $charges->fixed_charge,
                'total_charge'      =>  $charges->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>"Buy Card ",
                'message'       => "Buy card successful ".$masked_card,
                'image'         => get_image($user->image,'user-profile'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::CARD_BUY,
                'user_id'  => $user->id,
                'message'   => $notification_content,
            ]);

              //Push Notifications
              event(new UserNotificationEvent($notification_content,$user));
              send_push_notification(["user-".$user->id],[
                  'title'     => $notification_content['title'],
                  'body'      => $notification_content['message'],
                  'icon'      => $notification_content['image'],
              ]);

             //admin notification
             $notification_content['title'] = 'Buy Card Successful '.$masked_card.' Successful ('.$user->username.')';
             AdminNotification::create([
                 'type'      => NotificationConst::CARD_BUY,
                 'admin_id'  => 1,
                 'message'   => $notification_content,
             ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
    //card fund helper
    public function insertCardFund( $trx_id,$user,$wallet,$amount, $myCard ,$payable,$charges) {
        $trx_id = $trx_id;
        $authWallet = $wallet;
        $afterCharge = ($authWallet->balance - $payable);
        $details =[
            'card_info' =>   $myCard??'',
            'charges' =>  $charges,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $user->id,
                'user_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::VIRTUALCARD,
                'trx_id'                        => $trx_id,
                'request_amount'                => $amount,
                'payable'                       => $payable,
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::CARDFUND," ")),
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::RECEIVED,
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
    public function insertFundCardCharge($id,$charges,$user,$masked_card) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    =>  $charges->percent_charge,
                'fixed_charge'      =>  $charges->fixed_charge,
                'total_charge'      =>  $charges->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>"Card Fund ",
                'message'       => "Card fund successful card: ".$masked_card.' '.getAmount($charges->sender_amount,2).' '.$charges->sender_currency,
                'image'         => get_image($user->image,'user-profile'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::CARD_FUND,
                'user_id'  => $user->id,
                'message'   => $notification_content,
            ]);

             //Push Notifications
             event(new UserNotificationEvent($notification_content,$user));
             send_push_notification(["user-".$user->id],[
                 'title'     => $notification_content['title'],
                 'body'      => $notification_content['message'],
                 'icon'      => $notification_content['image'],
             ]);

            //admin notification
            $notification_content['title'] ="Card Fund Successful card: ".$masked_card.' '.getAmount($charges->sender_amount,2).' '.$charges->sender_currency.'('.$user->username.')';
            AdminNotification::create([
                'type'      => NotificationConst::CARD_FUND,
                'admin_id'  => 1,
                'message'   => $notification_content,
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
    //update user balance
    public function updateSenderWalletBalance($authWalle,$afterCharge) {
        $authWalle->update([
            'balance'   => $afterCharge,
        ]);
    }


    public function cardCallBack(Request $request){
        $body = @file_get_contents("php://input");
        $signature = (isset($_SERVER['HTTP_VERIF_HASH']) ? $_SERVER['HTTP_VERIF_HASH'] : '');
        if (!$signature) {
            exit();
        }
        $local_signature = env('SECRET_HASH');
        if ($signature !== $local_signature) {
            exit();
        }
        http_response_code(200);
        $response = json_decode($body);
        $trx = 'VC-' . str_random(6);
        if ($response->status == 'successful') {
            $card = VirtualCard::where('card_id', $response->CardId)->first();
            if ($card) {
                $card->amount = $response->balance;
                $card->save();

                //Transactions
                // $vt = new Virtualtransactions();
                // $vt->user_id = $card->user_id;
                // $vt->virtual_card_id = $card->id;
                // $vt->card_id = $card->card_id;
                // $vt->amount = $response->amount;
                // $vt->description = $response->description;
                // $vt->trx = $trx;
                // $vt->status = $response->status;
                // $vt->save();


                return true;
            }
            return true;
        }
        return false;
    }
    public function virtualCardCharge($sender_amount,$charges,$sender_wallet) {
        $data['sender_amount']          = $sender_amount;
        $data['sender_currency']        = $sender_wallet->currency->code;
        $data['sender_currency_rate']   = $sender_wallet->currency->rate;
        $data['percent_charge']         = ($sender_amount / 100) * $charges->percent_charge ?? 0;
        $data['fixed_charge']           = $sender_wallet->currency->rate * $charges->fixed_charge ?? 0;
        $data['total_charge']           = $data['percent_charge'] + $data['fixed_charge'];
        $data['sender_wallet_balance']  = $sender_wallet->balance;
        $data['payable']                = ($sender_amount * $sender_wallet->currency->rate) + $data['total_charge'];
        $data['base_currency']          = get_default_currency_code();

        return (object)$data;
    }

}
