<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wallet;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    public function list(){
        $list = Wallet::all();
        return response()->json($list);
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            "name" => 'required|unique:wallets',
            "address" => 'required',
            "link" => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => json_encode($validator->errors())]);
        }

        $wallet = new Wallet;
        $wallet->name = $request->name;
        $wallet->address = $request->address;
        // $wallet->coin = $request->coin;
        $wallet->link =$request->link;
        $wallet->save();

        return response()->json(['result'=>'ok','message'=>'Cartera creada con éxito']);
    }

    public function update(Request $request){
        $id =  $request->id;
        $validator = Validator::make($request->all(), [
            "name" => 'required|unique:wallets,id,'.$id,
            "address" => 'required',
            
            "link" => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => json_encode($validator->errors())]);
        }

        $wallet = Wallet::find($id);
        $wallet->name = $request->name;
        $wallet->address = $request->address;
        // $wallet->coin = $request->coin;
        $wallet->link =$request->link;
        $wallet->save();
       
        return response()->json(['result'=>'ok','message'=>'Datos actualizados con éxito.']);
    }

    public function delete(Request $request)
    {
        $Plan = Wallet::find($request->id);
        if($Plan){
            $Plan->delete();
        }
        return response()->json(['result'=>'ok','message'=>'Cartera eliminado con éxito.']);
    }
}
