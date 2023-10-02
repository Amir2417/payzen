<?php

namespace App\Http\Controllers\Agent\Auth;

use App\Constants\GlobalConst;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentPasswordReset;
use App\Providers\Admin\BasicSettingsProvider;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;

class ForgotPasswordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showForgotForm()
    {
        $page_title = setPageTitle("Forgot Password");
        return view('agent.auth.forgot-password.forgot',compact('page_title'));
    }


    /**
     * Send Verification code to user email/phone.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendCode(Request $request)
    {
        $request->validate([
            'credentials'   => "required|string|max:100",
        ]);
        $column = "username";
        if(check_email($request->credentials)) $column = "email";
        $user = Agent::where($column,$request->credentials)->first();
        if(!$user) {
            throw ValidationException::withMessages([
                'credentials'       => "Agent doesn't exists.",
            ]);
        }

        $token = generate_unique_string("agent_password_resets","token",80);
        $code = generate_random_code();

        try{
            AgentPasswordReset::where("agent_id",$user->id)->delete();
            $password_reset = AgentPasswordReset::create([
                'agent_id'       => $user->id,
                'token'         => $token,
                'code'          => $code,
            ]);
            $user->notify(new PasswordResetEmail($user,$password_reset));
        }catch(Exception $e) {
            return back()->with(['error' => ['Something went worng! Please try again.']]);
        }
        return redirect()->route('agent.password.forgot.code.verify.form',$token)->with(['success' => ['Varification code sended to your email address.']]);
    }


    public function showVerifyForm($token) {
        $page_title = setPageTitle("Verify Agent");
        $password_reset = AgentPasswordReset::where("token",$token)->first();
        if(!$password_reset) return redirect()->route('agent.password.forgot')->with(['error' => ['Password Reset Token Expired']]);
        $resend_time = 0;
        if(Carbon::now() <= $password_reset->created_at->addMinutes(GlobalConst::USER_PASS_RESEND_TIME_MINUTE)) {
            $resend_time = Carbon::now()->diffInSeconds($password_reset->created_at->addMinutes(GlobalConst::USER_PASS_RESEND_TIME_MINUTE));
        }
        $user_email = $password_reset->merchant->email ?? "";
        return view('agent.auth.forgot-password.verify',compact('page_title','token','user_email','resend_time'));
    }

    /**
     * OTP Verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifyCode(Request $request,$token)
    {
        $request->merge(['token' => $token]);
        $validated = Validator::make($request->all(),[
            'token'         => "required|string|exists:merchant_password_resets,token",
            'code'          => "required|numeric|exists:merchant_password_resets,code",
        ])->validate();

        $basic_settings = BasicSettingsProvider::get();
        $otp_exp_seconds = $basic_settings->otp_exp_seconds ?? 0;

        $password_reset = AgentPasswordReset::where("token",$token)->first();

        if(Carbon::now() >= $password_reset->created_at->addSeconds($otp_exp_seconds)) {
            foreach(AgentPasswordReset::get() as $item) {
                if(Carbon::now() >= $item->created_at->addSeconds($otp_exp_seconds)) {
                    $item->delete();
                }
            }
            return redirect()->route('agent.password.forgot')->with(['error' => ['Session expired. Please try again.']]);
        }

        if($password_reset->code != $validated['code']) {
            throw ValidationException::withMessages([
                'code'      => "Verification Otp is Invalid",
            ]);
        }

        return redirect()->route('agent.password.forgot.reset.form',$token);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function resendCode($token)
    {
        $password_reset = AgentPasswordReset::where('token',$token)->first();
        if(!$password_reset) return back()->with(['error' => ['Request token is invalid']]);
        if(Carbon::now() <= $password_reset->created_at->addMinutes(GlobalConst::USER_PASS_RESEND_TIME_MINUTE)) {
            throw ValidationException::withMessages([
                'code'      => 'You can resend verification code after '.Carbon::now()->diffInSeconds($password_reset->created_at->addMinutes(GlobalConst::USER_PASS_RESEND_TIME_MINUTE)). ' seconds',
            ]);
        }

        DB::beginTransaction();
        try{
            $update_data = [
                'code'          => generate_random_code(),
                'created_at'    => now(),
                'token'         => $token,
            ];
            DB::table('agent_password_resets')->where('token',$token)->update($update_data);
            $password_reset->merchant->notify(new PasswordResetEmail($password_reset->user,(object) $update_data));
            DB::commit();
        }catch(Exception $e) {
            DB::rollback();
            return back()->with(['error' => ['Something went worng. please try again']]);
        }
        return redirect()->route('agent.password.forgot.code.verify.form',$token)->with(['success' => ['Varification code resend success!']]);

    }


    public function showResetForm($token) {
        $page_title = setPageTitle("Reset Password");
        return view('agent.auth.forgot-password.reset',compact('page_title','token'));
    }


    public function resetPassword(Request $request,$token) {
        $basic_settings = BasicSettingsProvider::get();
        $passowrd_rule = "required|string|min:6|confirmed";
        if($basic_settings->secure_password) {
            $passowrd_rule = ["required",Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised(),"confirmed"];
        }

        $request->merge(['token' => $token]);
        $validated = Validator::make($request->all(),[
            'token'         => "required|string|exists:agent_password_resets,token",
            'password'      => $passowrd_rule,
        ])->validate();

        $password_reset = AgentPasswordReset::where("token",$token)->first();
        if(!$password_reset) {
            throw ValidationException::withMessages([
                'password'      => "Invalid Request. Please try again.",
            ]);
        }

        try{
            $password_reset->agent->update([
                'password'      => Hash::make($validated['password']),
            ]);
            $password_reset->delete();
        }catch(Exception $e) {
            return back()->with(['error' => ['Something went worng! Please try again.']]);
        }

        return redirect()->route('agent.login')->with(['success' => ['Password reset success. Please login with new password.']]);
    }
    //==================================recovery password by mobile start==========================================
    public function smsForgotForm()
    {
        $page_title = "Forgot Password";
        return view('agent.auth.sms-password.forgot',compact('page_title'));
    }
    public function sendForgotCode(Request $request)
    {
        $request->validate([
            'mobile'   => "required|max:100",
        ]);
        $column = "mobile";
        if(check_email($request->mobile)) $column = "email";
        $user = Agent::where($column,$request->mobile)->first();
        if(!$user) {
            throw ValidationException::withMessages([
                'mobile'       => "Agent doesn't exists.",
            ]);
        }

        $token = generate_unique_string("agent_password_resets","token",80);
        $code = generate_random_code();

        try{
            AgentPasswordReset::where("agent_id",$user->id)->delete();
            $password_reset = AgentPasswordReset::create([
                'agent_id'      => $user->id,
                'mobile'        => $request->mobile,
                'token'         => $token,
                'code'          => $code,
            ]);
            sendSms($user, 'PASS_RESET_CODE', [
                'code' => $code
            ]);
        }catch(Exception $e) {
            return back()->with(['error' => [$e->getMessage()]]);
        }
        return redirect()->route('agent.password.verify.code',$token)->with(['success' => ['Verification code sent to your phone number.']]);
    }
    public function smsVerifyCodeForm($token)
    {
        $page_title = "Verify SMS";
        $password_reset = AgentPasswordReset::where("token",$token)->first();
        if(!$password_reset) return redirect()->route('agent.password.forgot.mobile')->with(['error' => ['Password Reset Token Expired']]);
        $resend_time = 0;
        if(Carbon::now() <= $password_reset->created_at->addMinutes(GlobalConst::USER_PASS_RESEND_TIME_MINUTE)) {
            $resend_time = Carbon::now()->diffInSeconds($password_reset->created_at->addMinutes(GlobalConst::USER_PASS_RESEND_TIME_MINUTE));
        }
        $user_mobile = $password_reset->user->full_mobile ?? "";
        return view('agent.auth.sms-password.verify',compact('page_title','token','user_mobile','resend_time'));
    }
    public function smsResendCode($token)
    {
        $password_reset = AgentPasswordReset::where('token',$token)->first();

        if(!$password_reset) return back()->with(['error' => ['Request token is invalid']]);
        if(Carbon::now() <= $password_reset->created_at->addMinutes(GlobalConst::USER_PASS_RESEND_TIME_MINUTE)) {
            throw ValidationException::withMessages([
                'code'      => 'You can resend verification code after '.Carbon::now()->diffInSeconds($password_reset->created_at->addMinutes(GlobalConst::USER_PASS_RESEND_TIME_MINUTE)). ' seconds',
            ]);
        }
        DB::beginTransaction();
        try{
            $code = generate_random_code();
            $update_data = [
                'agent_id'      => $password_reset->agent->id,
                'mobile'       => $password_reset->agent->mobile,
                'token'         => $token,
                'code'          => $code,
            ];
            DB::table('agent_password_resets')->where('token',$token)->update($update_data);
            sendSms($password_reset->agent, 'PASS_RESET_CODE', [
                'code' => $code
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollback();
            return back()->with(['error' => [$e->getMessage()]]);
        }
        return redirect()->route('agent.password.verify.code',$token)->with(['success' => ['Verification code resend success!']]);
    }
    public function smsVerifyCode(Request $request,$token){
        $request->merge(['token' => $token]);
        $validated = Validator::make($request->all(),[
            'token'         => "required|string|exists:agent_password_resets,token",
            'code'      => "required|array",
            'code.*'    => "required|numeric",
        ])->validate();
        $code = $request->code;
        $code = implode("",$code);

        $basic_settings = BasicSettingsProvider::get();
        $otp_exp_seconds = $basic_settings->otp_exp_seconds ?? 0;

        $password_reset = AgentPasswordReset::where("token",$token)->first();
        if(!$password_reset){
            return back()->with(['error' => ['Invalid request']]);
        }
        if( $password_reset){
            if(Carbon::now() >= $password_reset->created_at->addSeconds($otp_exp_seconds)) {
                foreach(AgentPasswordReset::get() as $item) {
                    if(Carbon::now() >= $item->created_at->addSeconds($otp_exp_seconds)) {
                        $item->delete();
                    }
                }
                return redirect()->route('agent.password.forgot.mobile')->with(['error' => ['Session expired. Please try again.']]);
            }
        }
        if($password_reset->code != $code) {
            throw ValidationException::withMessages([
                'code'      => "Verification Otp is Invalid",
            ]);
        }
        return redirect()->route('agent.password.forgot.reset',$token)->with(['success' => ['Sms code verified successfully']]);
    }


    public function showResetPasswordForm($token){
        $page_title = "Reset Password";
        return view('agent.auth.sms-password.reset',compact('page_title','token'));
    }
    public function resetPasswordPost(Request $request,$token) {
        $basic_settings = BasicSettingsProvider::get();
        $passowrd_rule = "required|string|min:6|confirmed";
        if($basic_settings->secure_password) {
            $passowrd_rule = ["required",Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised(),"confirmed"];
        }

        $request->merge(['token' => $token]);
        $validated = Validator::make($request->all(),[
            'token'         => "required|string|exists:agent_password_resets,token",
            'password'      => $passowrd_rule,
        ])->validate();

        $password_reset = AgentPasswordReset::where("token",$token)->first();
        if(!$password_reset) {
            throw ValidationException::withMessages([
                'password'      => "Invalid Request. Please try again.",
            ]);
        }

        try{
            $password_reset->agent->update([
                'password'      => Hash::make($validated['password']),
            ]);
            $password_reset->delete();
        }catch(Exception $e) {
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }

        return redirect()->route('agent.login')->with(['success' => ['Password reset success. Please login with new password.']]);
    }
    //==================================recovery password by mobile end============================================

}
