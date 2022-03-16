<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\models\Plan;
use App\models\Userplan;
use App\models\License;
use Illuminate\Support\Facades\Validator;
use Auth;
class PlanController extends Controller
{
    // Front
    public function mark_plan_active(){

    }

    public function activate_plan(Request $request){
        $plan = Plan::find($request->id);
        
        if(!$plan){
            return 'no-existe';
        }


        $planPending =  Userplan::where('user_id',Auth::user()->id)->where('status','incompleto')->first();
        if($planPending){
            $planuser = $planPending;
        }else{
            $planUserEmpty = Userplan::where('user_id',Auth::user()->id)->where('status','vacio')->first();
            if(!$planUserEmpty){
                $planuser = new Userplan;
                $planuser->date_activated = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
                $planuser->date_end = \Carbon\Carbon::now()->addMonth($plan->duration)->format('Y-m-d H:i:s');
                $planuser->name = $plan->name;
                $planuser->cost = $plan->cost;
                $planuser->profit = $plan->profit;
                $planuser->total_profit = $plan->total_profit;
                $planuser->duration = $plan->duration;
                $planuser->charge_limit = $plan->charge_limit;
                $planuser->products = $plan->products;
                $planuser->plan_id = $plan->id;
                $planuser->user_id = Auth::user()->id;
                $planuser->save();
            }else{
                $planuser = $planUserEmpty;
            }
         
        }

        return response()->json($planuser);
    }
    // Admin
    public function list(){
        $list = Plan::all();
        $license = License::first()->cost;
        return response()->json(compact('list','license'));
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            "name" => 'required|unique:plans',
            "cost" => 'required',
            "profit" => 'required',
            "duration" => 'required',
            "charge_limit"=> 'required',
            "products"=> 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => json_encode($validator->errors())]);
        }

        $user = new Plan;
        $user->name = $request->name;
        $user->cost = $request->cost;
        $user->profit = $request->profit;
        if($user->cost != 0 and $user->profit != 0){
            $total_profit = ($user->cost * $user->profit) / 100;
        }else{
            $total_profit = 0;
        }
        $user->total_profit = $total_profit;
        $user->duration = $request->duration;
        $user->charge_limit = $request->charge_limit;
        $user->products = $request->products;
        $user->save();

        return response()->json(['result'=>'ok','message'=>'Plan Creado con éxito']);
    }

    public function update(Request $request){
        $id =  $request->id;
        $validator = Validator::make($request->all(), [
            "id"=>"required",
            "name" => 'required|unique:plans,id,'.$id,
            "cost" => 'required',
            "profit" => 'required',
            "duration" => 'required',
            "charge_limit"=> 'required',
            "products"=> 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => json_encode($validator->errors())]);
        }

        $user =  Plan::find($id);
        $user->name = $request->name;
        $user->cost = $request->cost;
        $user->profit = $request->profit;
        if($user->cost != 0 and $user->profit != 0){
            $total_profit = (str_replace(',','.',$request->cost) * str_replace(',','.',$request->profit)) / 100;
        }else{
            $total_profit = 0;
        }
        $user->total_profit =str_replace('.',',',$total_profit);
        $user->duration = $request->duration;
        $user->charge_limit =$request->charge_limit;
        $user->products = $request->products;
        $user->save();

       
        return response()->json(['result'=>'ok','message'=>'Datos actualizados con éxito.']);
    }

    public function delete(Request $request)
    {
        $Plan = Plan::find($request->id);
        if($Plan){
            $Plan->delete();
        }
        return response()->json(['result'=>'ok','message'=>'Plan eliminado con éxito.']);
    }

}

