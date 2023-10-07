<?php

namespace App\Http\Controllers\User;

use Exception;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Constants\GlobalConst;
use App\Models\Admin\Currency;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\BasicSettings;
use App\Constants\NotificationConst;
use App\Http\Controllers\Controller;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\TransactionSetting;
use Illuminate\Support\Facades\Validator;
use App\Traits\Transaction as TransactionTrait;
use App\Providers\Admin\BasicSettingsProvider;
use App\Notifications\User\SendMoney\SenderMail;
use App\Notifications\User\SendMoney\ReceiverMail;

class SendMoneyController extends Controller
{
    use TransactionTrait;
    protected  $trx_id;

    public function __construct()
    {
        $this->trx_id = 'SM'.getTrxNum();
    }
    public function index() {
        $page_title = "Send Money";
        $sender_wallets = UserWallet::auth()->whereHas('currency',function($q) {
            $q->where("sender",GlobalConst::ACTIVE)->where("status",GlobalConst::ACTIVE);
        })->active()->get();
        $receiver_wallets = Currency::receiver()->active()->get();
        $sendMoneyCharge = TransactionSetting::where('slug','transfer')->where('status',1)->first();
        $transactions = Transaction::auth()->senMoney()->latest()->take(10)->get();
        return view('user.sections.send-money.index',compact("page_title",'sendMoneyCharge','transactions',"sender_wallets","receiver_wallets"));
    }
    public function checkUser(Request $request){
        $email = $request->email;
        $exist['data'] = User::where('email',$email)->first();

        $user = auth()->user();
        if(@$exist['data'] && $user->email == @$exist['data']->email){
            return response()->json(['own'=>'Can\'t transfer/request to your own']);
        }
        return response($exist);
    }
    public function confirmed(Request $request) {
        $validated = Validator::make($request->all(),[
            'sender_amount'     => "required|numeric|gt:0",
            'sender_currency'   => "required|string|exists:currencies,code",
            'receiver_amount'   => "required|numeric|gt:0",
            'receiver_currency' => "required|string|exists:currencies,code",
            'email'          => "required|string",
        ])->validate();
        $sender_wallet = UserWallet::auth()->whereHas("currency",function($q) use ($validated) {
            $q->where("code",$validated['sender_currency'])->active();
        })->active()->first();
        if(!$sender_wallet) return back()->with(['error' => ['Your wallet isn\'t available with currency ('.$validated['sender_currency'].')']]);

        $receiver_currency = Currency::receiver()->active()->where('code',$validated['receiver_currency'])->first();
        if(!$receiver_currency) return back()->with(['error' => ['Currency ('.$validated['receiver_currency'].') isn\'t available for receive any remittance']]);

        $trx_charges =  userGroupTransactionsCharges(GlobalConst::TRANSFER);
        $charges = $this->transferCharges($validated['sender_amount'],$trx_charges,$sender_wallet,$receiver_currency);

        $sender_currency_rate = $sender_wallet->currency->rate;
        $min_amount = $trx_charges->min_limit * $sender_currency_rate;
        $max_amount = $trx_charges->max_limit * $sender_currency_rate;

        if($charges['sender_amount'] < $min_amount || $charges['sender_amount'] > $max_amount) {
            return back()->with(['error' => ['Please follow the transaction limit. (Min '.$min_amount . ' ' . $sender_wallet->currency->code .' - Max '.$max_amount. ' ' . $sender_wallet->currency->code . ')']]);
        }

        $field_name = "username";
        if(check_email($validated['email'])) {
            $field_name = "email";
        }

        $receiver = User::notAuth()->where($field_name,$validated['email'])->active()->first();
        if(!$receiver) return back()->with(['error' => ['Receiver doesn\'t exists or Receiver is temporary banned']]);

        $receiver_wallet = UserWallet::where("user_id",$receiver->id)->whereHas("currency",function($q) use ($receiver_currency){
            $q->receiver()->where("code",$receiver_currency->code);
        })->first();
        if(!$receiver_wallet) return back()->with(['error' => ['Receiver wallet not available']]);

        if($charges['payable'] > $sender_wallet->balance) return back()->with(['error' => ['Your wallet balance is insufficient']]);

        DB::beginTransaction();
        try{
            $trx_id = 'SM'.getTrxNum();
            // Sender TRX
            $inserted_id = DB::table("transactions")->insertGetId([
                'user_id'           => $sender_wallet->user->id,
                'user_wallet_id'    => $sender_wallet->id,
                'type'              => PaymentGatewayConst::TYPETRANSFERMONEY,
                'trx_id'            => $trx_id,
                'request_amount'    => $charges['sender_amount'],
                'payable'           => $charges['payable'],
                'available_balance' => $sender_wallet->balance - $charges['payable'],
                'attribute'         => PaymentGatewayConst::SEND,
                'details'           => json_encode(['receiver_username'=> $receiver_wallet->user->username,'receiver_email'=> $receiver_wallet->user->email,'sender_username'=> $sender_wallet->user->username,'sender_email'=> $sender_wallet->user->email,'charges' => $charges]),
                'status'            => GlobalConst::SUCCESS,
                'created_at'        => now(),
            ]);

            // Receiver TRX
            DB::table("transactions")->insert([
                'user_id'           => $receiver_wallet->user->id,
                'user_wallet_id'    => $receiver_wallet->id,
                'type'              => PaymentGatewayConst::TYPETRANSFERMONEY,
                'trx_id'            => $trx_id,
                'request_amount'    => $charges['receiver_amount'],
                'payable'           => $charges['receiver_amount'],
                'available_balance' => $receiver_wallet->balance + $charges['receiver_amount'],
                'attribute'         => PaymentGatewayConst::RECEIVED,
                'details'           => json_encode(['receiver_username'=> $receiver_wallet->user->username,'receiver_email'=> $receiver_wallet->user->email,'sender_username'=> $sender_wallet->user->username,'sender_email'=> $sender_wallet->user->email,'charges' => $charges]),
                'status'            => GlobalConst::SUCCESS,
                'created_at'        => now(),
            ]);

            $this->createTransactionChildRecords($inserted_id,(object) $charges);

            $sender_wallet->balance -= $charges['payable'];
            $sender_wallet->save();

            $receiver_wallet->balance += $charges['receiver_amount'];
            $receiver_wallet->save();
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            return redirect()->route('user.send.money.index')->with(['error' => ['Transaction failed! Something went wrong! Please try again']]);
        }

        return redirect()->route('user.send.money.index')->with(['success' => ['Transaction success']]);
    }

    public function transferCharges($sender_amount,$charges,$sender_wallet,$receiver_currency) {
        $exchange_rate = $receiver_currency->rate / $sender_wallet->currency->rate;

        $data['exchange_rate']          = $exchange_rate;
        $data['sender_amount']          = $sender_amount;
        $data['sender_currency']        = $sender_wallet->currency->code;
        $data['receiver_amount']        = $sender_amount * $exchange_rate;
        $data['receiver_currency']      = $receiver_currency->code;
        $data['percent_charge']         = ($sender_amount / 100) * $charges->percent_charge ?? 0;
        $data['fixed_charge']           = $sender_wallet->currency->rate * $charges->fixed_charge ?? 0;
        $data['total_charge']           = $data['percent_charge'] + $data['fixed_charge'];
        $data['sender_wallet_balance']  = $sender_wallet->balance;
        $data['payable']                = $sender_amount + $data['total_charge'];
        return $data;
    }
}
