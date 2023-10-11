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
        $stripe_cards   = StripeCard::where('agent_id',auth()->user()->id)->orderByDESC('id')->paginate(6);

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
    /**
     * Method for delele stripe card information
     * @param $id
     */
    public function delete(Request $request,$id){
        $stripe_card        = StripeCard::where('id',$id)->first();
        if(!$stripe_card) return back()->with(['error' => ['Sorry! Stripe card is not found.']]);
        try{
            $stripe_card->delete();
        }catch(Exception $e){
            return back()->with(['success' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Stripe Card deleted successfully.']]);

    }
    /**
     * Method for add stripe card if there is no stripe card when add money
     * @param $gateway
     * @return view
     */
    public function add($gateway){
        $page_title     = "Add Stripe Card";
        return view('agent.sections.stripe-card.add',compact(
            'page_title',
            'gateway'
        ));
    }
    /**
     * Method for store stripe data information
     * @param $gateway
     * @param \Illuminate\Http\Request $request
     */
    public function storeData(Request $request,$gateway){
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
        return redirect()->route('agent.add.money.payment',$gateway)->with(['success'  => ['Stripe Card Added Successfully.']]);
    }
}
