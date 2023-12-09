<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentReferredUser;
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
        $refer_users    = AgentReferredUser::where('refer_agent_id',$auth_user->id)->with(['agent' => function($query){
            $query->with(['referAgents']);
        }])->paginate(10);

        return view('agent.sections.refer.index',compact(
            'page_title',
            'auth_user',
            'refer_users'
        ));
    }
}
