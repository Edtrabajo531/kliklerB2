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
    // Admin
    public function update_license(Request $request){
        $validator = Validator::make($request->all(), [
            "cost" => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => json_encode($validator->errors())]);
        }

        $license = License::first();
        $license->cost = str_replace(',','.',$request->cost);
        $license->save();

        return response()->json(['result'=>'ok','message'=>'Licencia actualizada con éxito.']);
    }

    public function list(){
        $list = Plan::all();
        $license = License::first()->cost;
        $planReview = Userplan::where('status','revision')->where('user_id',Auth::user()->id)->first();
        return response()->json(compact('list','license','planReview'));
    }
    public function listAdmin(){
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
            "products"=> 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => json_encode($validator->errors())]);
        }

        $user = new Plan;
        $user->name = $request->name;
        $user->cost =  str_replace(',','.',$request->cost);
        $user->profit =  str_replace(',','.',$request->profit);
        $user->duration = $request->duration;
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
            "products"=> 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => json_encode($validator->errors())]);
        }

        $user =  Plan::find($id);
        $user->name = $request->name;
        $user->cost =  str_replace(',','.',$request->cost);
        $user->profit =  str_replace(',','.',$request->profit);
        $user->duration = $request->duration;
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

