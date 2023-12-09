<?php

namespace App\Http\Controllers\Agent;

use App\Constants\GlobalConst;
use App\Http\Controllers\Controller;
use App\Models\Admin\SetupKyc;
use App\Models\AgentAuthorization;
use App\Models\UserAuthorization;
use App\Notifications\User\Auth\SendAuthorizationCode;
use App\Providers\Admin\BasicSettingsProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ControlDynamicInputFields;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthorizationController extends Controller
{
    use ControlDynamicInputFields;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showMailFrom($token)
    {
        
        $page_title = "Mail Authorization";
        return view('agent.auth.authorize.verify-mail',compact("page_title","token"));
    }
    public function showSmsFromRegister()
    {
        $page_title = "Mobile Verification";
        $registerInfo =   session()->get('register_info');
        $register_info =   (object)session()->get('register_info');
        if($registerInfo == null){
            return redirect()->route('agent.register');
        }
        return view('agent.auth.authorize.verify-sms',compact("page_title","register_info"));
    }
    public function showSmsFrom()
    {
        if (auth()->check()) {
            $user = auth()->user();
            if (!$user->status) {
                Auth::logout();
                return redirect()->route('agent.login')->with(['error' => ['Your account disabled,please contact with admin!!']]);
            }elseif (!$user->sms_verified) {
                $page_title = 'SMS Authorization';
                $code = generate_random_code();
                $data = [
                    'agent_id'     => $user->id,
                    'mobile'       =>  $user->mobile,
                    'code'          => $code,
                    'token'         => generate_unique_string("agent_authorizations","token",200),
                    'created_at'    => now(),
                ];
                DB::beginTransaction();
                try{
                    AgentAuthorization::where("agent_id",$user->id)->delete();
                    DB::table("agent_authorizations")->insert($data);
                    sendSms($user, 'SVER_CODE', [
                        'code' => $code
                    ]);
                    DB::commit();
                }catch(Exception $e) {
                    DB::rollBack();
                    return back()->with(['error' => ['Something went worng! Please try again']]);
                }
                return view('agent.auth.authorize.verify-sms-auth',compact("page_title"));
            }else{
                return redirect()->route('agent.dashboard');
            }

        }

        return redirect()->route('user.login');
    }

    public function smsResendCode(){
        $user = auth()->user();
        $resend = AgentAuthorization::where("agent_id",$user->id)->first();
        if( $resend){
            if(Carbon::now() <= $resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)) {
                throw ValidationException::withMessages([
                    'code'      => 'You can resend verification code after '.Carbon::now()->diffInSeconds($resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)). ' seconds',
                ]);
            }
        }
        $code = generate_random_code();
        $data = [
            'agent_id'       =>  $user->id,
            'mobile'       =>  $user->mobile,
            'code'          => $code,
            'token'         => generate_unique_string("agent_authorizations","token",200),
            'created_at'    => now(),
        ];

        DB::beginTransaction();
        try{
            AgentAuthorization::where("agent_id",$user->id)->delete();
            DB::table("agent_authorizations")->insert($data);
            sendSms($user, 'SVER_CODE', [
                'code' => $code
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            return back()->with(['error' => ['Something went worng! Please try again']]);
        }

        return back()->with(['success' => ['Varification code resend success!']]);
    }
    public function smsVerifyCode(Request $request){
        $user = auth()->user();
        $request->validate([
            'code'      => "required|array",
            'code.*'    => "required|numeric",
        ]);
        $code = $request->code;
        $code = implode("",$code);
        $otp_exp_sec = BasicSettingsProvider::get()->otp_exp_seconds ?? GlobalConst::DEFAULT_TOKEN_EXP_SEC;
        $auth_column = AgentAuthorization::where("agent_id",$user->id)->where("code",$code)->first();
        if(!$auth_column){
            return back()->with(['error' => ['Verification code does not match']]);
        }
        if($auth_column->created_at->addSeconds($otp_exp_sec) < now()) {
            $this->authLogout($request);
            return back()->with(['error' => ['Session expired. Please try again']]);
        }
        try{
            $auth_column->agent->update([
                'sms_verified'    => true,
            ]);
            $auth_column->delete();
        }catch(Exception $e) {
            $this->authLogout($request);
            return redirect()->route('agent.login')->with(['error' => ['Something went worng! Please try again']]);
        }

        return redirect()->intended(route("agent.dashboard"))->with(['success' => ['Account successfully verified']]);
    }

    /**
     * Verify authorizaation code.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function mailVerify(Request $request,$token)
    {
        $request->merge(['token' => $token]);
        $request->validate([
            'token'     => "required|string|exists:agent_authorizations,token",
            'code'      => "required|array",
            'code.*'    => "required|numeric",
        ]);
        $code = $request->code;
        $code = implode("",$code);
        $otp_exp_sec = BasicSettingsProvider::get()->otp_exp_seconds ?? GlobalConst::DEFAULT_TOKEN_EXP_SEC;
        $auth_column = AgentAuthorization::where("token",$request->token)->where("code",$code)->first();
        if(!$auth_column){
            return back()->with(['error' => ['Verification code does not match']]);
        }


        if($auth_column->created_at->addSeconds($otp_exp_sec) < now()) {
            $this->authLogout($request);
            return redirect()->route('index')->with(['error' => ['Session expired. Please try again']]);
        }

        try{
            $auth_column->agent->update([
                'email_verified'    => true,
            ]);
            $auth_column->delete();
        }catch(Exception $e) {
            
            $this->authLogout($request);
            return redirect()->route('agent.login')->with(['error' => ['Something went worng! Please try again']]);
        }

        return redirect()->intended(route("agent.dashboard"))->with(['success' => ['Account successfully verified']]);
    }
    public function resendCode()
    {
        $user = auth()->user();
        $resend = AgentAuthorization::where("agent_id",$user->id)->first();
        if(Carbon::now() <= $resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)) {
            throw ValidationException::withMessages([
                'code'      => 'You can resend verification code after '.Carbon::now()->diffInSeconds($resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)). ' seconds',
            ]);
        }
        $data = [
            'agent_id'       =>  $user->id,
            'code'          => generate_random_code(),
            'token'         => generate_unique_string("agent_authorizations","token",200),
            'created_at'    => now(),
        ];

        DB::beginTransaction();
        try{
            AgentAuthorization::where("agent_id",$user->id)->delete();
            DB::table("agent_authorizations")->insert($data);
            $user->notify(new SendAuthorizationCode((object) $data));
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            return back()->with(['error' => ['Something went worng! Please try again']]);
        }

        return redirect()->route('agent.authorize.mail',$data['token'])->with(['success' => ['Varification code resend success!']]);

    }

    public function authLogout(Request $request) {
        auth()->guard("agent")->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function showKycFrom() {
        $user = auth()->user();
        if($user->kyc_verified == GlobalConst::VERIFIED) return back()->with(['success' => ['You are already KYC Verified Agent']]);
        $page_title = "KYC Verification";
        $user_kyc = SetupKyc::agentKyc()->first();
        if(!$user_kyc) return back();
        $kyc_data = $user_kyc->fields;
        $kyc_fields = [];
        if($kyc_data) {
            $kyc_fields = array_reverse($kyc_data);
        }
        return view('agent.auth.authorize.verify-kyc',compact("page_title","kyc_fields"));
    }

    public function kycSubmit(Request $request) {

        $user = auth()->user();
        if($user->kyc_verified == GlobalConst::VERIFIED) return back()->with(['success' => ['You are already KYC Verified Agent']]);

        $user_kyc_fields = SetupKyc::agentKyc()->first()->fields ?? [];
        $validation_rules = $this->generateValidationRules($user_kyc_fields);

        $validated = Validator::make($request->all(),$validation_rules)->validate();
        $get_values = $this->placeValueWithFields($user_kyc_fields,$validated);

        $create = [
            'agent_id'       => auth()->user()->id,
            'data'          => json_encode($get_values),
            'created_at'    => now(),
        ];

        DB::beginTransaction();
        try{
            DB::table('agent_kyc_data')->updateOrInsert(["agent_id" => $user->id],$create);
            $user->update([
                'kyc_verified'  => GlobalConst::PENDING,
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $user->update([
                'kyc_verified'  => GlobalConst::DEFAULT,
            ]);
            $this->generatedFieldsFilesDelete($get_values);
            return back()->with(['error' => ['Something went worng! Please try again']]);
        }

        return redirect()->route("agent.profile.index")->with(['success' => ['KYC information successfully submited']]);
    }
    public function showGoogle2FAForm() {
        $page_title =  "Authorize Google Two Factor";
        dd("test");
        return view('agent.auth.authorize.verify-google-2fa',compact('page_title'));
    }

    public function google2FASubmit(Request $request) {


        $request->validate([
            'code'      => "required|array",
            'code.*'    => "required|numeric",
        ]);
        $code = $request->code;
        $code = implode("",$code);
        $user = auth()->user();
        if(!$user->two_factor_secret) {
            return back()->with(['warning' => ['Your secret key not stored properly. Please contact with system administrator']]);
        }

        if(google_2fa_verify($user->two_factor_secret,$code)) {
            $user->update([
                'two_factor_verified'   => true,
            ]);
            return redirect()->intended(route('agent.dashboard'));
        }

        return back()->with(['warning' => ['Faild to login. Please try again']]);
    }
}
