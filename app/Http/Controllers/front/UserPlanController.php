<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserPlan;
use App\Models\Plan;
use App\Models\Bank;
use App\Models\Image;
use App\Models\User;
use App\Models\License;
use App\Models\Inversion;
use Illuminate\Support\Facades\File;
use App\Models\Wallet;
use Auth;
use Mail;
use ImageIntervention;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
class UserPlanController extends Controller
{

public function list(){
    $plan = Userplan::select(
        "user_plans.*", 
        "users.alias as user_alias",
        "users.name as user_name"
    )
    ->leftJoin("users", "users.id", "=", "user_plans.user_id")
    ->where('user_plans.status','revision')
    ->orWhere('user_plans.status','rechazado')
    ->orWhere('user_plans.status','finalizado')
    ->orWhere('user_plans.status','activo')
    ->get();

    return response()->json(['list' => $plan]);
}

public function my_plan(Request $request){
    $userplan = Userplan::where('user_id',Auth::user()->id)->where('status','activo')->first();
    if(!$userplan){
        $userplan = Plan::where('profit','0')->first();
    }
   
    return response()->json(compact('userplan'));
}

public function getPlanAdmin(Request $request){
    $userplan = UserPlan::find($request->id);
    $user = User::find($userplan->user_id);
    $images = Image::where('userplan_id',$request->id)->get();
  
    return response()->json(compact('userplan','images','user'));
}

// activar plan admin
public function activatePlan(Request $request){
    
    $userplan = UserPlan::find($request->id);
    
    $planActive = UserPlan::where('user_id',$userplan->user_id)->where('status','activo')->first();

    if($planActive){
        $planActive->status = 'finalizado';
        $planActive->save();
    }

    $userplan->date_activated = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
    $userplan->date_end = \Carbon\Carbon::now()->addMonth($userplan->duration)->format('Y-m-d H:i:s');
    $userplan->observations = "";
    $userplan->status = 'activo';
    $userplan->save();



    $inversionLast = Inversion::where('user_id',$userplan->user_id)->where('status','last')->first();
    if($inversionLast){
        $inversionLast->status = 'other';
        $inversionLast->save();
    }
    
    $inversion = new Inversion;
    $inversion->date_start = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
    $inversion->date_end = $userplan->date_end;
    $inversion->user_plan_id = $userplan->id;
    $inversion->user_id = $userplan->user_id;
    $inversion->inversion = $userplan->inversion;
    $inversion->save();

    $user = User::find($inversion->user_id);
    $user->points = $user->points + $userplan->license;
    $user->inversion_total = $user->inversion_total + $inversion->inversion;
    $user->minimum_charge = $userplan->minimum_charge;
    $user->license_pay = 'Si';
    $user->save();


    $data = ['data'=>['plan'=>$userplan]];
    Mail::send('mails.confirm_activation_plan',$data,function($message){
        $message->subject('Respuesta solicitud de activación de plan KLIKLER "Confirmada"');
        $message->to("eavc53189@gmail.com");
    });
}

public function rejectPlan(Request $request){
    $userplan = UserPlan::find($request->id);
    $userplan->date_activated = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
    $userplan->date_end = \Carbon\Carbon::now()->addMonth($userplan->duration)->format('Y-m-d H:i:s');
    $userplan->observations = $request->observations;
    $userplan->status = 'rechazado';
    $userplan->save();

    $data = ['data'=>['plan'=>$userplan]];
    Mail::send('mails.reject_activation_plan',$data,function($message){
        $message->subject('Respuesta solicitud de activación de Plan KLIKLER "Rechazada"');
        $message->to("eavc53189@gmail.com");
    });

}

// front
public function plan_under_review(Request $request){
    $plan = UserPlan::where('status','revision')->where('user_id',Auth::user()->id)->first();
    if(!$plan){
        $plan = null;
    }
    return $plan; 
}

public function request_activation(Request $request){
    $planActive = UserPlan::where('user_id',Auth::user()->id)->where('status','revision')->first();
    if($planActive){
        return response()->json(['error' => 'Ya posee un plan activo.']); 
    }

    $validator = Validator::make($request->all(), [
        'id' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
    }

    $userplan = UserPlan::find($request->id);
    $userplan->status = 'revision';
    $userplan->date_request = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
    $userplan->save();

    $user = Auth::user();
    $data = ['data'=>['user'=>$user,'plan'=>$userplan]];

    Mail::send('mails.request_activation_plan',$data,function($message){
        $message->subject('Solicitud de activación de Plan KLIKLER');
        $message->to("eavc53189@gmail.com");
    });
}

public function upload_file(Request $request){

    $validator = Validator::make($request->all(), [
        'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp',
    ]);

    if ($validator->fails()) {
        return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
    }

    $width_min = 350;
    $width_max = 1200;
    $folder = Auth::user()->id."/"."plan/".$request->id;

    if ($request->hasFile('file')) {
        $file = ImageIntervention::make($request->file('file')->getRealPath());
        if ($file->width() < $width_min) {
            return response()->json(["result" => "error", "message" => "La imagen debe tener un tamaño superior a " . $width_min . " píxeles."]);
        }

        $extension = $request->file('file')->getClientOriginalExtension();
        $fileName   = \Carbon\Carbon::now()->format('dmYHms').Str::random(10);

        $url_path = asset('images/user/'.$folder.'/' . $fileName . '.' . $extension);
        $local_path = public_path('images/user/'.$folder.'/'. $fileName . '.' . $extension);
        $image = new Image;

        // $image->name = $fileName.$extension;
        $image->url_path = $url_path;
        $image->local_path = $local_path;
        $image->type = 'userplan';

        $image->userplan_id = $request->id;
        $image->user_id = Auth::user()->id;
    
        $image->save();

        // make dir
        if (!File::exists('images')) {
            File::makeDirectory('images');
        }

        if (!File::exists('images/user')) {
            File::makeDirectory('images/user');
        }

        if (!File::exists('images/user/'.Auth::user()->id)) {
            File::makeDirectory('images/user/'.Auth::user()->id);
        }

        if (!File::exists('images/user/'.Auth::user()->id.'/plan')) {
            File::makeDirectory('images/user/'.Auth::user()->id.'/plan');
        }

        if (!File::exists('images/user/'.$folder)) {
            File::makeDirectory('images/user/'.$folder);
        }

        //move image to img folder
        if ($file->width() > $width_max) {
            $img = $file->resize($width_max, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save('images/user/'.$folder.'/'. $fileName . '.' . $extension);
        } else {
            $file->save('images/user/'.$folder.'/' . $fileName . '.' . $extension);
        }

        return response()->json(["result" => "success", "message" => "Archivo subido con éxito."]);
    } else {
        return response()->json("Hubo un error al intentar subir este archivo.");
    }
}


    private function deleteImage($local_path){

        if (File::exists($local_path)) {
            $local_path = str_replace("\\", "/", $local_path);
            $positionExt = strripos($local_path, '.');
            $ext = substr($local_path, $positionExt);
            $path_xs = str_replace($ext, '-xs' . $ext, $local_path);
            $path_sm = str_replace($ext, '-sm' . $ext, $local_path);
            File::delete($path_xs);
            File::delete($path_sm);
            File::delete($local_path);
        }
        
    }

public function delete_file(Request $request){
    $img = Image::find($request->id);
    if($img){
        $this->deleteImage($img->local_path);
        $img->delete();
    }
    
    return response()->json('Archivo borrado con éxito.');
}
// }
// if($request->type == 'transaction'){
//     $images = Image::where('user_id',Auth::user()->id)->where('type','transaction')->get();
// }else{
//     $images = Image::where('user_id',Auth::user()->id)->where('type','verification')->get();
// }

// return response()->json($images);

public function get(Request $request){
    $userplan = UserPlan::where('id',$request->id)->where('user_id',Auth::user()->id)->first();
    $banks = Bank::all();
    $wallets = Wallet::all();
    $images = Image::where('userplan_id',$request->id)->get();
    $license = License::first()->cost;
    return response()->json(compact('banks','wallets','userplan','images','license'));
   
}
public function get_accounts_payment(Request $request){
    $bankss = Bank::all()->first();
    $wallets = Wallet::all()->first();
    return response()->json(compact('banks','wallets'));
 }
 
public function insertAccountsPayment(Request $request){
    $validator = Validator::make($request->all(), [
        'id'=> 'required',
        'bank_id' => 'required',
        'wallet_id' => 'required',
    ]);


    if ($validator->fails()) {
        return response()->json(['result' => 'error-validation', 'errors' => $validator->errors()]);
    }

    $userplan = UserPlan::find($request->id);
    $userplan->bank_id =  $request->bank_id;
    $userplan->wallet_id =  $request->wallet_id;
    // BANK
    $bank = Bank::find($userplan->bank_id);
    $userplan->nameBank = $bank->name;
    $userplan->holderBank = $bank->holder;
    $userplan->identificationBank = $bank->identification;
    $userplan->typeBank = $bank->type;
    $userplan->numberAccountBank = $bank->numberAccount;
    $userplan->save();
    // WALLET
    $wallet = Wallet::find($userplan->bank_id);
    $userplan->nameWallet = $wallet->name;
    $userplan->addressWallet = $wallet->address;
    $userplan->coinWallet = $wallet->coin;
    $userplan->linkWallet = $wallet->link;
    $userplan->save();

}

public function insertAmount(Request $request){
    
    $validator = Validator::make($request->all(), [
        "id"=> 'required',
    ]);

    if ($validator->fails()) {
        return response()->json(['result' => 'error-validation', 'errors' => json_encode($validator->errors())]);
    }

    $userplan = UserPlan::find($request->id);
    $inversion =  str_replace(',','.',$request->inversion);
    
    if (floatval($inversion) < floatval( $userplan->cost)) {
        return response()->json(['result' => 'error-validation', 'errors' =>json_encode(["inversion"=>"La inversión debe ser superior a ".str_replace('.',',',$userplan->cost)])]);
    }
    
    $userplan->minimum_charge = ($inversion * $userplan->profit ) / 100 ;
    $userplan->total_profit = (($inversion * $userplan->profit ) / 100) * $userplan->duration;
    $userplan->date_activated = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
    $userplan->date_end = \Carbon\Carbon::now()->addMonth($userplan->duration)->format('Y-m-d H:i:s');
    $userplan->inversion = $inversion;

    /*$userplan->pay_in_dollars = ($inversion * 25) / 100;
    $userplan->pay_in_btc = ($inversion * 75) / 100;*/

    /*$userplan->pay_in_dollars = ($inversion * 25) / 100;
    $userplan->pay_in_btc = ($inversion * 75) / 100;*/
    
    $license = License::first()->cost;
    $user = Auth::user();
    if($user->license_pay == 'Si'){
        $license = 0;
    }
    
    $userplan->license = $license;
    $userplan->total_pay = $license + $inversion;
    $userplan->total_pay_dollars = $license + $userplan->pay_in_dollars;

    $userplan->save();
    
    return response()->json("ok");

}

// Configurar plan antes de activar
public function activate_plan(Request $request){
    $planReview = UserPlan::where('status','revision')->where('user_id',Auth::user()->id)->first();
    if($planReview){
        return response()->json("plan-review");
    }

    $planActive = UserPlan::where('status','activo')->where('user_id',Auth::user()->id)->first();
    if($planActive){
        return response()->json("plan-active");
    }

    $plan = Plan::find($request->id);
    
    if(!$plan){
        return 'no-existe';
    }

    $planPending =  UserPlan::where('user_id',Auth::user()->id)->where('status','incompleto')->where('plan_id',$plan->id)->first();
    if($planPending != null){
        $userplan = $planPending;
    }else{
        // borrar inompleto
        $planPending =  UserPlan::where('user_id',Auth::user()->id)->where('status','incompleto')->first();
        if($planPending){
            $images = Image::where('userplan_id', $planPending->id)->get();
            foreach($images as $img){
                $local_path = $img->local_path;
                $this->deleteImage($local_path);
                $img->delete();
            }
            $planPending->delete();
        }

        $planUserEmpty = UserPlan::where('user_id',Auth::user()->id)->where('status','vacio')->where('plan_id',$plan->id)->first();
        if(!$planUserEmpty){
            // Borrar vacio
            $planUserEmpty = UserPlan::where('user_id',Auth::user()->id)->where('status','vacio')->first();
            if($planUserEmpty){
                $images = Image::where('userplan_id', $planUserEmpty->id)->get();
                foreach($images as $img){
                    $local_path = $img->local_path;
                    $this->deleteImage($local_path);
                    $img->delete();
                }
                $planUserEmpty->delete();
            }

            $userplan = new UserPlan;
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
        }else{
            $userplan = $planUserEmpty;
        }
     
    }

    if(Auth::user()->phone and Auth::user()->date_of_birth){
        $datauser = "complete";
    }else{
        $datauser = "incomplete";
    }

    return response()->json(compact('plan','userplan','datauser'));
}
}
