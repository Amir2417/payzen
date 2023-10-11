<?php

namespace App\Http\Controllers\Agent;

use Exception;
use App\Models\StripeCard;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class StripeCardController extends Controller
{
    /**
     * Method for show all the stripe cards information
     * @return view
     */
    public function index(){
        $page_title     = "Stripe Card";
        $stripe_cards   = StripeCard::orderByDESC('id')->paginate(10);

        return view('agent.sections.stripe-card.index',compact(
            'page_title',
            'stripe_cards'
        ));
    }
    /**
     * Method for show the stripe card create page
     * @return view
     */
    public function create(){
        $page_title     = "Add Stripe Card";

        return view('agent.sections.stripe-card.create',compact(
            'page_title'
        ));
    }
    /**
     * Method for store stripe card information
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request){
        $validator  = Validator::make($request->all(),[
            'name'              => 'required',
            'card_number'       => 'required',
            'expiration_date'   => 'required',
            'cvc_code'          => 'required',
        ]);
        if($validator->fails()){
            return back()->withErrors($validator->errors())->withInput();
        }
        $validated                      = $validator->validate();
        $validated['agent_id']          = auth()->user()->id;
        $validated['name']              = encrypt($validated['name']);
        $validated['card_number']       = encrypt($validated['card_number']);
        $validated['expiration_date']   = encrypt($validated['expiration_date']);
        $validated['cvc_code']          = encrypt($validated['cvc_code']);
        try{
            StripeCard::insert($validated);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return redirect()->route('agent.stripe.card.index')->with(['success'  => ['Stripe Card Added Successfully.']]);
    }
}
