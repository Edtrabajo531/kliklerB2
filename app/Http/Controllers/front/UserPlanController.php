<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserPlan;
use App\Models\Plan;
use App\Models\Bank;
use App\Models\Wallet;
use Auth;
use Illuminate\Support\Facades\Validator;

class UserPlanController extends Controller
{
   // Front
public function get(Request $request){
   $userplan = UserPlan::where('id',$request->id)->where('user_id',Auth::user()->id)->first();
   $bankss = Bank::all()->first();
    $wallets = Wallet::all()->first();
    return response()->json(compact('banks','wallets','userplan'));
   
}
public function get_accounts_payment(Request $request){
    $bankss = Bank::all()->first();
    $wallets = Wallet::all()->first();
    return response()->json(compact('banks','wallets'));
 }

public function insertAmount(Request $request){
    
    $validator = Validator::make($request->all(), [
        "id"=> 'required',
    ]);

    if ($validator->fails()) {
        return response()->json(['result' => 'error-validation', 'errors' => json_encode($validator->errors())]);
    }

    $userplan = Userplan::find($request->id);
    $validator = Validator::make($request->all(), [
        "inversion"=> 'required|numeric|min:'. $userplan->cost,
    ]);

    if ($validator->fails()) {
        return response()->json(['result' => 'error-validation', 'errors' => json_encode($validator->errors())]);
    }

    $userplan->inversion = floatval($request->inversion);
    $userplan->minimum_charge = (floatval($request->inversion) * intval($userplan->profit) ) / 100 ;
    $userplan->total_profit = $userplan->minimum_charge * $userplan->duration;
    $userplan->date_activated = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
    $userplan->date_end = \Carbon\Carbon::now()->addMonth($userplan->duration)->format('Y-m-d H:i:s');
    $userplan->save();
    
    return response()->json("ok");
            // $userplan->minimum_charge = $plan->minimum_charge;
//                 status
// date_activated
// date_end
// name
// cost
// inversion
// profit
// total_profit
// minimum_charge
// minimum_charge_invertion
// duration
// products
// plan_id
// user_id
}

public function mark_plan_active(){

}

public function activate_plan(Request $request){
    $plan = Plan::find($request->id);
    
    if(!$plan){
        return 'no-existe';
    }

    $planPending =  Userplan::where('user_id',Auth::user()->id)->where('status','incompleto')->first();
    if($planPending){
        $userplan = $planPending;
    }else{
        $planUserEmpty = Userplan::where('user_id',Auth::user()->id)->where('status','vacio')->first();
        if(!$planUserEmpty){
            $userplan = new Userplan;
           
            $userplan->name = $plan->name;
            $userplan->cost = $plan->cost;
            // $userplan->inversion = ;
            $userplan->profit = $plan->profit;
            $userplan->total_profit = $plan->total_profit;
            $userplan->duration = $plan->duration;
            // $userplan->minimum_charge = $plan->minimum_charge;
            $userplan->products = $plan->products;
            $userplan->plan_id = $plan->id;
            $userplan->user_id = Auth::user()->id;
            $userplan->save();

//                 status
// date_activated
// date_end
// name
// cost
// inversion
// profit
// total_profit
// minimum_charge
// minimum_charge_invertion
// duration
// products
// plan_id
// user_id
        }else{
            $userplan = $planUserEmpty;
        }
     
    }

    return response()->json(compact('plan','userplan'));
}
}
