<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\Request;
use App\Constants\GlobalConst;
use App\Http\Controllers\Controller;
use App\Models\Admin\ReferralSetting;

class ReferralSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = "Referral Settings";
        $referral_settings = ReferralSetting::first();
        return view('admin.sections.settings.referral.index',compact(
            'page_title',
            'referral_settings'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'bonus'             => 'required_if:status,1|numeric|gt:0',
            'wallet_type'       => 'required|in:c_balance,p_balance',
            'mail'              => 'required|boolean',
            'status'            => 'required|boolean',
        ]);

        $wallet_type = [
            'c_balance'     => GlobalConst::CURRENT_BALANCE,
            'p_balance'     => GlobalConst::PROFIT_BALANCE,
        ];

        $validated['wallet_type']   = $wallet_type[$validated['wallet_type']];

        try{
            $settings = ReferralSetting::first();
            if($settings) {
                $settings->update($validated);
            }else {
                $settings = ReferralSetting::create($validated);
            }
        }catch(Exception $e) {
            return back()->with(['error' => ['Something went wrong! Please try again']]);
        }

        return back()->with(['success' => ['Settings update successfully!']]);

    }
}
