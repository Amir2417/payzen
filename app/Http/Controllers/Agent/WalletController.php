<?php

namespace App\Http\Controllers\Agent;

use Exception;
use App\Models\AgentWallet;
use Illuminate\Http\Request;
use App\Constants\GlobalConst;
use App\Http\Helpers\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = "All Wallets";
        $fiat_wallets = AgentWallet::auth()->whereHas("currency",function($q) {
            return $q->where("type",GlobalConst::FIAT);
        })->orderByDesc("balance")->get();

        $crypto_wallets = AgentWallet::auth()->whereHas("currency",function($q) {
            return $q->where("type",GlobalConst::CRYPTO);
        })->orderByDesc("balance")->get();
        
        return view('agent.sections.wallets.index',compact("page_title","fiat_wallets","crypto_wallets"));
    }


    public function balance(Request $request) {
        $validator = Validator::make($request->all(),[
            'target'        => "required|string",
        ]);

        if($validator->fails()) {
            return Response::error($validator->errors(),null,400);
        }

        $validated = $validator->validate();

        try{
            $wallet = AgentWallet::auth()->whereHas("currency",function($q) use ($validated) {
                $q->where("code",$validated['target']);
            })->first();
        }catch(Exception $e) {
            $error = ['error' => ['Something went worng!. Please try again.']];
            return Response::error($error,null,500);
        }

        if(!$wallet) {
            $error = ['error' => ['Your '.($validated['target']).' wallet not found.']];
            return Response::error($error,null,404);
        }

        $success = ['success' => ['Data collected successfully!']];
        return Response::success($success,$wallet->balance,200);

    }
}
