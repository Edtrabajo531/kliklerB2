<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\models\Plan;
use Illuminate\Support\Facades\Validator;
class PlanController extends Controller
{
    public function list(){
        $list = Plan::all();
        return response()->json($list);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            "name" => 'required|unique:plans',
            "cost" => 'required',
            "profit" => 'required',
            "duration" => 'required',
            "charge_limit"=> 'required',
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
        $user->save();

        return response()->json(['result'=>'ok','message'=>'Plan Creado con éxito']);
    }

    public function update(Request $request){
        $id =  $request->id;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'holder' => 'required',
            'identification' => 'required',
            'type' => 'required',
            'numberAccount' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => json_encode($validator->errors())]);
        }

        $user = Plan::find($id);
        $user->name = $request->name;
        $user->holder = $request->holder;
        $user->identification = $request->identification;
        $user->type = $request->type;
        $user->numberAccount = $request->numberAccount;
        $user->save();

        return response()->json(['result'=>'ok','message'=>'Datos actualizados con éxito.']);
    }

    public function delete(Request $request)
    {
        $Plan = Plan::find($request->id);
        if($Plan){
            $Plan->delete();
        }
        return response()->json(['result'=>'ok','message'=>'Cuenta Bancaria eliminada con éxito.']);
    }

}

