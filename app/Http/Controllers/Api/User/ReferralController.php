<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\ReferredUser;
use Illuminate\Http\Request;
use App\Http\Helpers\Api\Helpers;
class ReferralController extends Controller
{
    public function referralUser(){
        $user = auth()->user();
        $referralUsers = ReferredUser::where('refer_user_id',$user->id)->get()->map(function($data){
            return[
                'name' => $data->user->fullname,
                'referral_id' => $data->user->referral_id,
                'referred_user' => count($data->user->referUsers),
                
            ];
        });
        $data =[
            'register_url'          => setRoute('user.register',$user->referral_id),
            'total_referral_user'   => count($referralUsers),
            'referralUsers'         => $referralUsers,
            
        ];
        $message =  ['success'=>['Referral Information']];
        return Helpers::success($data,$message);
    }
}
