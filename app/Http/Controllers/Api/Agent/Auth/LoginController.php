<?php

namespace App\Http\Controllers\Api\Agent\Auth;

use Exception;
use App\Models\Agent;
use Illuminate\Http\Request;
use App\Constants\GlobalConst;
use App\Models\Admin\SetupKyc;
use Illuminate\Support\Facades\DB;
use App\Traits\Agent\LoggedInUsers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Traits\Agent\RegisteredUsers;
use App\Traits\ControlDynamicInputFields;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Providers\Admin\BasicSettingsProvider;
use App\Http\Helpers\Api\Helpers as ApiHelpers;
use App\Models\AgentQrCode;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers, RegisteredUsers,LoggedInUsers,ControlDynamicInputFields;
    protected $basic_settings;
    public function __construct()
    {
        $this->basic_settings = BasicSettingsProvider::get();
    }
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:50',
            'password' => 'required|min:6',
        ]);

        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return ApiHelpers::validation($error);
        }

        $user = Agent::where('email',$request->email)->first();
        if(!$user){
       
            $error = ['error'=>['User does not exist']];
            return ApiHelpers::validation($error);
        }
        if (Hash::check($request->password, $user->password)) {
            if($user->status == 0){
                $error = ['error'=>['Account Has been Suspended']];
                return ApiHelpers::validation($error);
            }
            $user->two_factor_verified = false;
            $user->save();
            $this->refreshUserWallets($user);
            $this->createLoginLog($user);
            $this->createQr($user);
            $token = $user->createToken('user_token')->accessToken;

            $data = ['token' => $token, 'user' => $user, ];
            $message =  ['success'=>['Login Successful']];
            return ApiHelpers::success($data,$message);

        } else {
            $error = ['error'=>['Incorrect Password']];
            return ApiHelpers::error($error);
        }

    }

    // register agent
    public function register(Request $request){
        
        $basic_settings = $this->basic_settings;
        $passowrd_rule = "required|string|min:6|confirmed";
        if($basic_settings->secure_password) {
            $passowrd_rule = ["required","confirmed",Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()];
        }
        if( $basic_settings->agree_policy){
            $agree ='required';
        }else{
            $agree ='';
        }
        
        $validator = Validator::make($request->all(), [
            'firstname'     => 'required|string|max:60',
            'lastname'      => 'required|string|max:60',
            'email'         => 'required|string|email|max:150|unique:users,email',
            'password'      => $passowrd_rule,
            'country'       => 'required|string|max:60',
            'city'       => 'required|string|max:20',
            'phone_code'    => 'required|string|max:20',
            'phone'         => 'required|string|max:20',
            'zip_code'         => 'required|string|max:20',
            'refer'         => 'nullable|string|exists:agents,referral_id',
            'agree'         =>  $agree,

        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return ApiHelpers::validation($error);
        }
        
        if($basic_settings->kyc_verification == true){
            $user_kyc_fields = SetupKyc::agentKyc()->first()->fields ?? [];
            $validation_rules = $this->generateValidationRules($user_kyc_fields);
            $validated = Validator::make($request->all(), $validation_rules);

            if ($validated->fails()) {
                $message =  ['error' => $validated->errors()->all()];
                return ApiHelpers::error($message);
            }
            $validated = $validated->validate();
            $get_values = $this->registerPlaceValueWithFields($user_kyc_fields, $validated);
        }
        $data = $request->all();
        $mobile        = remove_speacial_char($data['phone']);
        $mobile_code   = remove_speacial_char($data['phone_code']);
        $complete_phone             =  $mobile_code . $mobile;
        $referral_id       = generate_unique_string('users','referral_id',8,'number');
        
        $email = Agent::orWhere('email',$data['email'])->first();
        if($email){
            $error = ['error'=>['Email address already exist']];
            return ApiHelpers::validation($error);
        }
        $mobile_validate = Agent::where('mobile', $mobile)->orWhere('full_mobile',$complete_phone)->first();
        if($mobile_validate){
            $error = ['error'=>['Mobile number already exist']];
            return ApiHelpers::validation($error);
        }

        //User Create
        $user = new Agent();
        $user->firstname = isset($data['firstname']) ? $data['firstname'] : null;
        $user->lastname = isset($data['lastname']) ? $data['lastname'] : null;
        $user->email = strtolower(trim($data['email']));
        $user->mobile =  $mobile;
        $user->mobile_code =  $mobile_code;
        $user->full_mobile =    $complete_phone;
        $user->referral_id =    $referral_id;
        $user->password = Hash::make($data['password']);
        $user->username = make_username($data['firstname'],$data['lastname']);
        // $user->image = 'default.png';
        $user->address = [
            'address' => isset($data['address']) ? $data['address'] : '',
            'city' => isset($data['city']) ? $data['city'] : '',
            'zip' => isset($data['zip_code']) ? $data['zip_code'] : '',
            'country' =>isset($data['country']) ? $data['country'] : '',
            'state' => isset($data['state']) ? $data['state'] : '',
        ];
        $user->status = 1;
        $user->email_verified = true;
        $user->sms_verified =  ($basic_settings->sms_verification == true) ? true : true;
        $user->kyc_verified =  ($basic_settings->kyc_verification == true) ? false : true;
        $user->save();
        if( $user && $basic_settings->kyc_verification == true){
            $create = [
                'agent_id'       => $user->id,
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
                $error = ['error'=>['Something went worng! Please try again']];
                return ApiHelpers::validation($error);
            }

           }
        $token = $user->createToken('user_token')->accessToken;
        $this->createUserWallets($user);
        $this->createAsReferUserIfExists($request, $user);
        $this->createNewUserRegisterBonus($user);
        $this->createQr($user);
        $data = ['token' => $token, 'user' => $user, ];
        $message =  ['success'=>['Registration Successful']];
        return ApiHelpers::success($data,$message);

    }

    public function logout(){
        Auth::user()->token()->revoke();
        $message = ['success'=>['Logout Successful']];
        return ApiHelpers::onlysuccess($message);

    }
    public function createQr($user){
		$user = $user;
	    $qrCode = $user->qrCode()->first();
        $in['agent_id'] = $user->id;;
        $in['qr_code'] =  $user->email;
	    if(!$qrCode){
            AgentQrCode::create($in);
	    }else{
            $qrCode->fill($in)->save();
        }
	    return $qrCode;
	}
}
