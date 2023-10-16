<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ReferredUser;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    /**
     * Method for view the user status page
     * @return view
     */
    public function index(){
        $page_title     = "Refer Page";
        $auth_user      =  auth()->user();
        $refer_users    = ReferredUser::where('refer_user_id',$auth_user->id)->with(['user' => function($query){
            $query->with(['referUsers']);
        }])->paginate(10);

        return view('user.sections.refer.index',compact(
            'page_title',
            'auth_user',
            'refer_users'
        ));
    }
}
