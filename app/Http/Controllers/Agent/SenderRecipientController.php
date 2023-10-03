<?php

namespace App\Http\Controllers\Agent;

use App\Constants\GlobalConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Models\Admin\ReceiverCounty;
use App\Models\Agent;
use App\Models\AgentRecipient;
use App\Models\RemitanceBankDeposit;
use App\Models\RemitanceCashPickup;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SenderRecipientController extends Controller
{
    public function index() 
    {
        $page_title = "My Sender Recipient";
        $token = (object)session()->get('sender_remittance_token');
        if(@$token ->transacion_type != null && @$token ->receiver_country == null){
            $recipients =  AgentRecipient::auth()->sender()->where('type',$token->transacion_type)->orderByDesc("id")->paginate(12);
        }elseif(@$token ->transacion_type != null && @$token ->receiver_country != null){
            $recipients =  AgentRecipient::auth()->sender()->where('type',$token->transacion_type)->where('country',@$token->receiver_country)->orderByDesc("id")->paginate(12);
        }else{
            $recipients = AgentRecipient::auth()->sender()->orderByDesc("id")->paginate(12);
        }
        return view('agent.sections.sender_recipient.index',compact('page_title','recipients'));
    }
    public function addReceipient(){
        $page_title = "Add Recipient";
        $receiverCountries = ReceiverCounty::active()->get();
        $banks = RemitanceBankDeposit::active()->latest()->get();
        $cashPickups = RemitanceCashPickup::active()->latest()->get();
        return view('agent.sections.sender_recipient.add',compact('page_title','receiverCountries','banks','cashPickups'));
    }
    public function storeReceipient(Request $request){
        $user = auth()->user();
        if($request->transaction_type == 'bank-transfer') {
            $bankRules = 'required|string';
        }else {
            $bankRules = 'nullable|string';
        }

        if($request->transaction_type == 'cash-pickup') {
            $cashPickupRules = "required|string";
        }else {
            $cashPickupRules = "nullable";
        }
        $request->validate([
            'transaction_type'              =>'required|string',
            'country'                      =>'required',
            'firstname'                      =>'required|string',
            'lastname'                      =>'required|string',
            'mobile'                      =>"required",
            'mobile_code'                      =>'required',
            'city'                      =>'required|string',
            'address'                      =>'required|string',
            'state'                      =>'required|string',
            'zip'                      =>'required|string',
            'bank'                      => $bankRules,
            'cash_pickup'               => $cashPickupRules,

        ]);
            $checkMobile = AgentRecipient::auth()->sender()->where('mobile',$request->mobile)->first();
            if($checkMobile){
                return back()->with(['error' => ['This mobile number already used as a recipients.']]);
            }
            $country = ReceiverCounty::where('id',$request->country)->first();
            if(!$country){
                return back()->with(['error' => ['Please select a valid country']]);
            }
            $countryId = $country->id;

        if($request->transaction_type == 'bank-transfer') {
            $alias  = $request->bank;
            $details = RemitanceBankDeposit::where('alias',$alias)->first();
            if( !$details){
                return back()->with(['error' => ['Please select a valid bank']]);
            }
        }elseif($request->transaction_type == 'cash-pickup'){
            $alias  = $request->cash_pickup;
            $details = RemitanceCashPickup::where('alias',$alias)->first();
            if( !$details){
                return back()->with(['error' => ['Please select a valid cash pickup']]);
            }
        }elseif($request->transaction_type == "wallet-to-wallet-transfer"){
            $receiver = Agent::where('mobile',(int)$request->mobile)->orWhere('full_mobile',$request->mobile)->first();
            if( !$receiver){
                return back()->with(['error' => ['Agent not found']]);
            }
            $details = $receiver;
            $alias  = $request->transaction_type;

        }

        $in['agent_id'] =  $user->id;
        $in['country'] =   $countryId;
        $in['type'] = $request->transaction_type;
        $in['recipient_type'] = GlobalConst::SENDER;
        $in['alias'] =   $alias;
        $in['firstname'] = $request->firstname;
        $in['lastname'] = $request->lastname;
        $in['state'] = $request->state;
        $in['mobile_code'] = remove_speacial_char($request->mobile_code);
        $in['mobile'] = remove_speacial_char($request->mobile_code) == "880"?(int)$request->mobile:$request->mobile ;
        $in['city'] = $request->city;
        $in['address'] = $request->address;
        $in['zip_code'] = $request->zip;
        $in['details'] = json_encode($details);
        try{
            AgentRecipient::create($in);
            return redirect()->route('agent.sender.recipient.index')->with(['success' => ['Sender recipient save successfully']]);
        }catch(Exception $e) {
            return back()->with(['error' => ["Something is wrong"]]);
        }

    }
    public function editReceipient($id){
        $page_title = "Edit Recipient";
        $countries = ReceiverCounty::active()->get();
        $banks = RemitanceBankDeposit::active()->latest()->get();
        $pickup_points = RemitanceCashPickup::active()->latest()->get();
        $data =  AgentRecipient::auth()->sender()->with('agent','receiver_country')->where('id',$id)->first();
        if( !$data){
            return back()->with(['error' => ['Sorry, invalid request']]);
        }
        return view('agent.sections.sender_recipient.edit',compact('page_title','countries','banks','pickup_points','data'));
    }
    public function updateReceipient(Request $request){
        $user = auth()->user();
        $data = AgentRecipient::auth()->sender()->with('agent','receiver_country')->where('id',$request->id)->first();
        if($request->transaction_type == 'bank-transfer') {
            $bankRules = 'required|string';
        }else {
            $bankRules = 'nullable|string';
        }

        if($request->transaction_type == 'cash-pickup') {
            $cashPickupRules = "required|string";
        }else {
            $cashPickupRules = "nullable";
        }
        $request->validate([
        'transaction_type'              =>'required|string',
        'country'                      =>'required',
        'firstname'                      =>'required|string',
        'lastname'                      =>'required|string',
        'mobile'                      =>"required",
        'mobile_code'                      =>'required',
        'city'                      =>'required|string',
        'address'                      =>'required|string',
        'state'                      =>'required|string',
        'zip'                      =>'required|string',
        'bank'                      => $bankRules,
        'cash_pickup'               => $cashPickupRules,

        ]);
        $checkMobile = AgentRecipient::where('id','!=',$data->id)->auth()->sender()->where('mobile',$request->mobile)->first();
        if($checkMobile){
            return back()->with(['error' => ['This mobile number already used as a recipients.']]);
        }

        $country = ReceiverCounty::where('id',$request->country)->first();
        if(!$country){
            return back()->with(['error' => ['Please select a valid country']]);
        }
        $countryId = $country->id;



        if($request->transaction_type == 'bank-transfer') {
            $alias  = $request->bank;
            $details = RemitanceBankDeposit::where('alias',$alias)->first();
            if( !$details){
                return back()->with(['error' => ['Please select a valid bank']]);
            }
        }elseif($request->transaction_type == 'cash-pickup'){
            $alias  = $request->cash_pickup;
            $details = RemitanceCashPickup::where('alias',$alias)->first();
            if( !$details){
                return back()->with(['error' => ['Please select a valid cash pickup']]);
            }
        }elseif($request->transaction_type == "wallet-to-wallet-transfer"){
            $receiver = Agent::where('mobile',(int)$request->mobile)->orWhere('full_mobile',$request->mobile)->first();
            if( !$receiver){
                return back()->with(['error' => ['Agent not found']]);
            }
            $details = $receiver;
            $alias  = $request->transaction_type;

        }

        $in['agent_id'] =  $user->id;
        $in['country'] =   $countryId;
        $in['type'] = $request->transaction_type;
        $in['recipient_type'] = GlobalConst::SENDER;
        $in['alias'] =   $alias;
        $in['firstname'] = $request->firstname;
        $in['lastname'] = $request->lastname;
        $in['state'] = $request->state;
        $in['mobile_code'] = remove_speacial_char($request->mobile_code);
        $in['mobile'] = remove_speacial_char($request->mobile_code) == "880"?(int)$request->mobile:$request->mobile ;
        $in['city'] = $request->city;
        $in['address'] = $request->address;
        $in['zip_code'] = $request->zip;
        $in['details'] = json_encode($details);
        try{
            $data->fill($in)->save();
            return redirect()->route('agent.sender.recipient.index')->with(['success' => ['Sender recipient updated successfully']]);
        }catch(Exception $e) {
            return back()->with(['error' => ["Something is wrong"]]);
        }

    }
    public function deleteReceipient(Request $request) {
        $validator = Validator::make($request->all(),[
            'target'        => 'required|string|exists:agent_recipients,id',
        ]);
        $validated = $validator->validate();
        $receipient = AgentRecipient::auth()->sender()->where("id",$validated['target'])->first();
        try{
            $receipient->delete();
        }catch(Exception $e) {
            return back()->with(['error' => ['Something went worng! Please try again.']]);
        }

        return back()->with(['success' => ['Sender recipient deleted successfully!']]);
    }
    //get dynamic fields
    public function getTrxTypeInputs(Request $request) {
        $validator = Validator::make($request->all(),[
            'data'          => "required|string"
        ]);
        if($validator->fails()) {
            return Response::error($validator->errors());
        }
        $validated = $validator->validate();


        switch($validated['data']){
            case Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER):
                $countries = ReceiverCounty::active()->get();
                return view('agent.components.recipient.trx-type-fields.wallet-to-wallet',compact('countries'));
                break;
            case Str::slug(GlobalConst::TRX_CASH_PICKUP);
                $countries = ReceiverCounty::active()->get();
                $pickup_points =  RemitanceCashPickup::active()->latest()->get();
                return view('agent.components.recipient.trx-type-fields.cash-pickup',compact('countries','pickup_points'));
                break;
            case Str::slug(GlobalConst::TRX_BANK_TRANSFER);
                $countries = ReceiverCounty::active()->get();
                $banks =  RemitanceBankDeposit::active()->latest()->get();
                return view('agent.components.recipient.trx-type-fields.bank-deposit',compact('countries','banks'));

            default:
                return Response::error(['Oops! Data not found or section is under maintenance']);
        }
        return Response::error(['error' => ['Something went wrong! Please try again']]);
    }
    public function getTrxTypeInputsEdit(Request $request) {
        $validator = Validator::make($request->all(),[
            'data'          => "required|string"
        ]);
        if($validator->fails()) {
            return Response::error($validator->errors());
        }
        $validated = $validator->validate();

        switch($validated['data']){
            case Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER):
                $countries = ReceiverCounty::active()->get();
                return view('agent.components.recipient.trx-type-fields.edit.wallet-to-wallet',compact('countries'));
                break;
            case Str::slug(GlobalConst::TRX_CASH_PICKUP);
                $countries = ReceiverCounty::active()->get();
                $pickup_points =  RemitanceCashPickup::active()->latest()->get();
                return view('agent.components.recipient.trx-type-fields.edit.cash-pickup',compact('countries','pickup_points'));
                break;
            case Str::slug(GlobalConst::TRX_BANK_TRANSFER);
                $countries = ReceiverCounty::active()->get();
                $banks =  RemitanceBankDeposit::active()->latest()->get();
                return view('agent.components.recipient.trx-type-fields.edit.bank-deposit',compact('countries','banks'));

            default:
                return Response::error(['Oops! Data not found or section is under maintenance']);
        }
        return Response::error(['error' => ['Something went wrong! Please try again']]);
    }

    public function sendRemittance($id){
        $recipient = AgentRecipient::auth()->sender()->where("id",$id)->first();
        $token = session()->get('sender_remittance_token');
        $in['receiver_country'] = $recipient->country;
        $in['transacion_type'] = $recipient->type;
        $in['sender_recipient'] = $recipient->id;
        $in['sender_recipient'] = $recipient->id;
        $in['receiver_recipient'] = $token['receiver_recipient']??'';
        $in['receive_amount'] = $token['receive_amount']??0;
        Session::put('sender_remittance_token',$in);
        return redirect()->route('agent.remittance.index');

    }
}
