<?php

namespace App\Traits\Agent;

use Exception;
use App\Models\Agent;
use App\Models\AgentWallet;
use Illuminate\Http\Request;
use App\Constants\GlobalConst;
use App\Models\Admin\Currency;
use App\Models\AgentReferredUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use App\Models\Admin\ReferralSetting;
use App\Constants\PaymentGatewayConst;
use App\Providers\Admin\CurrencyProvider;
use App\Providers\Admin\BasicSettingsProvider;

trait RegisteredUsers {
    protected function createUserWallets($user) {
        $currencies = Currency::active()->roleHasOne()->pluck("id")->toArray();
        $wallets = [];
        foreach($currencies as $currency_id) {
            $wallets[] = [
                'agent_id'       => $user->id,
                'currency_id'   => $currency_id,
                'balance'       => 0,
                'status'        => true,
                'created_at'    => now(),
            ];
        }

        try{
            AgentWallet::insert($wallets);
        }catch(Exception $e) {
            // handle error
            $this->guard()->logout();
            $user->delete();
            return $this->breakAuthentication("Faild to create wallet! Please try again");
        }
    }


    protected function breakAuthentication($error) {
        return back()->with(['error' => [$error]]);
    }

    protected function createAsReferUserIfExists(Request $request, $user) {
        if($request->has('refer') && $request->refer != null && Agent::where('referral_id',trim($request->refer))->exists()) {
            $refer_user = Agent::where('referral_id',trim($request->refer))->first();
            
            $refer_user_id = $refer_user->id; // who refer this new user
            try{
                AgentReferredUser::create([
                    'refer_agent_id'     => $refer_user_id,
                    'new_agent_id'       => $user->id,
                    'created_at'        => now(), 
                ]);
                $this->referAgentActions($refer_user); // who refer the new user
            }catch(Exception $e) {
                throw new Exception($e);
            }
        }
    }
    protected function referAgentActions($refer_user) // who refer the new user
    {
        
        $this->createReferBonus($refer_user); // create refer bonus as per level commission
        
    }
    protected function createReferBonus($user) {
        $refer_level = ReferralSetting::first();
        $refer_user = $user;

        $system_super_admin = get_super_admin();
        $default_currency = CurrencyProvider::default();

        $refer_user_wallet = $refer_user->wallets()->whereHas('currency',function($query) use ($default_currency) {
            $query->where('code',$default_currency->code);
        })->first();

        DB::beginTransaction();
        try{
            DB::table('transactions')->insert([
                'type'              => PaymentGatewayConst::TYPEREFERBONUS,
                'trx_id'            => generate_unique_string('transactions','trx_id',16),
                'user_type'         => GlobalConst::ADMIN,
                'agent_id'           => $system_super_admin->id,
                'agent_wallet_id'    => $refer_user_wallet->id,
                'request_amount'    => $refer_level->bonus,
                'request_currency'  => $default_currency->code,
                'payable'           => $refer_level->bonus,
                'receive_amount'    => $refer_level->bonus,
                'receiver_type'     => GlobalConst::AGENT,
                'receiver_id'       => $refer_user->id,
                'available_balance' => $refer_user_wallet->balance + $refer_level->bonus,
                'status'            => PaymentGatewayConst::STATUSSUCCESS,
                'created_at'        => now(),
            ]);

            // Update user wallet balance
            DB::table($refer_user_wallet->getTable())->where('id',$refer_user_wallet->id)->increment('balance',$refer_level->bonus);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
    protected function createNewUserRegisterBonus($user) {
        $referral_settings = ReferralSetting::first();
        if($referral_settings && $referral_settings->status == GlobalConst::ACTIVE && $referral_settings->bonus > 0) {
            
            // need to add bonus in user wallet
            $default_currency = CurrencyProvider::default();
            $system_super_admin = get_super_admin();
            
            $user_wallet = $user->wallets()->whereHas('currency',function($query) use ($default_currency) {
                $query->where('code',$default_currency->code);
            })->first();

            // create bonus transaction
            DB::beginTransaction();
            try{
                DB::table('transactions')->insert([
                    'type'              => PaymentGatewayConst::TYPEBONUS,
                    'trx_id'            => generate_unique_string('transactions','trx_id',16),
                    'user_type'         => GlobalConst::ADMIN,
                    'agent_id'           => $system_super_admin->id,
                    'agent_wallet_id'    => $user_wallet->id,
                    'request_amount'    => $referral_settings->bonus,
                    'request_currency'  => $default_currency->code,
                    'payable'           => 0,
                    'receive_amount'    => $referral_settings->bonus,
                    'receiver_type'     => GlobalConst::AGENT,
                    'receiver_id'       => $user->id,
                    'available_balance' => $user_wallet->balance + $referral_settings->bonus,
                    'payment_currency'  => $default_currency->code,
                    'remark'            => Lang::get("Registration Bonus"),
                    'status'            => PaymentGatewayConst::STATUSSUCCESS,
                    'created_at'        => now(),
                ]);

                $wallet_type = [
                    GlobalConst::CURRENT_BALANCE    => 'balance',
                    GlobalConst::PROFIT_BALANCE     => 'profit_balance',
                ];

                // Update user wallet balance
                DB::table($user_wallet->getTable())->where('id',$user_wallet->id)->increment($wallet_type[$referral_settings->wallet_type],$referral_settings->bonus);

                // Send mail if enable
                if($referral_settings->mail == GlobalConst::ACTIVE) {

                    $user->notify((new RegistrationBonusNotification([
                        'user_name'         => $user->fullname,
                        'system_name'       => BasicSettingsProvider::get()->site_name,
                        'bonus_amount'      => $referral_settings->bonus,
                        'currency_code'     => $default_currency->code,
                    ]))->delay(20));
                }

                DB::commit();
            }catch(Exception $e) {
                DB::rollBack();
                throw new Exception($e->getMessage());
            }
        }
    }
}
