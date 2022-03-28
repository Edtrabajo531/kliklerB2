<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use ImageIntervention;
use Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Auth;
use App\Models\State;
use App\Models\City;
use App\Models\UserPlan;

class UserController extends Controller
{
    
    public function getAuth(){
        $user = Auth::user();
        $states = State::all();
        $cities = City::all();
        $user_plan = UserPlan::where('status','activo')->where('user_id',Auth::user()->id)->first();
        $porcentage_month = 0;
        $profit_month = 0;
        if($user_plan){
            $porcentage_month = $user_plan->profit;
            $profit_month = $user_plan->minimum_charge;
        }
        return response()->json(compact('user','states','cities','porcentage_month','profit_month'));
    }
    
    public function prueba(){
         return "PRUEBA";
    }

    public function viewMail(){
        // return "XX";
        
        return view('mails.confirm_activation_plan');
    }
    public function pruebaMail(){
        $data = ['data'=>['email'=>"eavc53189@gmail.com",'token'=>"wrewr"]];
        Mail::send('mails.confirm_email',$data,function($message){
            $message->subject('Confirma tu cuenta de KLIKLERr');
            $message->to("eavc53189@gmail.com");
        });
        return "enviado";
    }
  
    
    public function recover_password_request(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => $validator->errors()]);
        }
        $user = User::where('email',$request->email)->first();
        if(!$user){
            return response()->json(['result' => 'error-validation', 'errors' =>json_encode(["email"=>"El correo no existe en nuestra base de datos."])]);
        }

        $token_password = str_replace('/', '', Hash::make(\Carbon\Carbon::now()->format("YmdHis").Str::random(10)));
        $user->token_password = $token_password;
        $user->date_token_password = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
        $user->save();

        $data = ['data'=>['email'=>$user->email,'token_password'=>$token_password]];

        Mail::send('mails.recover_password',$data,function($message) use($user){
            $message->subject('Restablecer contraseña KLIKLER');
            $message->to($user->email);
        });

       return response()->json(['result'=>'ok']);

    }


    public function recover_password(Request $request){
        $validator = Validator::make($request->all(), [
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => $validator->errors()]);
        }

        $user = User::whereNotNull("token_password")->where('email',$request->email)->where('token_password',$request->token)->first();
        if(!$user){
            return response()->json(['result'=>'error','message'=>'La solicitud es incorrecta o se ha vencido.']);
        }

        if($user->date_token_password){
            if($user->date_token_password > \Carbon\Carbon::now()->format("Y-m-d H:i:s")){
                return response()->json(['result'=>'error','message'=>'La solicitud de recuperar contraseña se ha vencido.']);
            }
        }

        $user->token_password  = null;
        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json(['result'=>'ok','message'=>'Contraseña actualizada con éxito.']);
    }

    public function recover_password_verify(Request $request){

        // return response()->json(['result'=>'error','message'=>$request->token]);
        $user = User::whereNotNull("token_password")->where('email',$request->email)->where('token_password',$request->token)->first();
        if(!$user){
            return response()->json(['result'=>'error','message'=>'La solicitud es incorrecta o se ha vencido.']);
        }
        if($user->date_token_password){
            if($user->date_token_password > \Carbon\Carbon::now()->format("Y-m-d H:i:s")){
                return response()->json(['result'=>'error','message'=>'La solicitud de recuperar contraseña se ha vencido.']);
            }
        }
        return response()->json('ok');
    }

  
    public function get_cities_select(Request $request){
        $cities = City::where('state_id',$request->id)->get();
        return response()->json($cities);
    }

    public function get_user_data(Request $request){
        $user = Auth::user();
        $states = State::all();
        $cities = [];
        if($user->state_id){
            $cities = City::where('state_id',$user->state_id)->get();
        }

        return response()->json(compact('user','states','cities'));
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'alias_or_email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => $validator->errors()]);
        }
        // verifica si existe usuario
        $user = User::where("alias", $request->alias_or_email)->orWhere("email", $request->alias_or_email)->first();
        if ($user == Null) {

            return response()->json(['result' => 'error-validation', 'errors' =>json_encode(["alias_or_email"=>"El alias o correo no ha sido registrado."])]);
        }

        if ($user->email_verified_at == Null && $user->role == 'cliente') {
            return response()->json(['result' => 'correo no verificado','correo'=>$user->email]);
            // return response()->json(['result' => 'error-validation', 'errors' =>json_encode(["alias_or_email"=>"El correo de esta cuenta aún no ha sido verificado."])]);
        }

        if($user->status == 'disabled'){
            return response()->json(['result' => 'error-validation', 'errors' =>json_encode(["alias_or_email"=>"Esta cuenta ha sido bloqueada."])]);

        }

        if (Hash::check($request->password, $user->password)) {
            $token = JWTAuth::fromUser($user);
            $user = $user->only('id', 'alias', 'email', 'name','last_name','role');
            $user['token'] = $token;
            $user['expiration'] = \Carbon\Carbon::now()->format('Y/m/d H:i:s');
            return response()->json($user);
        }

        return response()->json(['result' => 'error-validation', 'errors' =>json_encode(["password"=>"La contraseña ingresada es incorrecta."])]);

    }

    public function register(Request $request){

        $validator = Validator::make($request->all(), [
            'alias' => 'unique:users',
            'email' => 'unique:users',
            'name' => 'required',
            'password' => 'required|min:8',
            'password_repeat' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => json_encode($validator->errors())]);
        }

        if($request->password != $request->password_repeat){
            return response()->json(['result' => 'error-validation', 'errors' =>json_encode(["password_repeat"=>"Las contraseñas deben coincidir."])]);
        }

        $user = new User;
        $user->alias = $request->alias;
        $user->email = $request->email;
        $user->name = $request->name;
        $token_email = str_replace('/', '', Hash::make(\Carbon\Carbon::now()->format("YmdHis").Str::random(10)));
        $user->token_email = $token_email;
        $user->role = "cliente";
        $user->password = bcrypt($request->password);
        $user->save();

        $data = ['data'=>['email'=>$user->email,'token'=>$token_email]];

        Mail::send('mails.confirm_email',$data,function($message) use($user){
            $message->subject('Confirma tu cuenta de KLIKLER');
            $message->to($user->email);
        });
  

        return response()->json(['result'=>'ok','message'=>'Se ha enviado un mensaje a su correo electrónico con el código de confirmación para completar el registro.']);
    }

    public function confirm_mail(Request $request){
        $user = User::where("email",$request->email)->first();

        if( $user == Null){
            return redirect()->to(env("ENDPOINT_FRONT")."?account=no-existe");
        }

        if($user->email_verified_at){
            return redirect()->to(env("ENDPOINT_FRONT").'?cuenta=confirmada');
        }

        if($user->token_email != Null and $user->token_email == $request->token){
            // $user->token_email = Null;
            $user->email_verified_at = \Carbon\Carbon::now()->format("Y-m-d H:i:s");
            $user->save();
            return redirect()->to(env("ENDPOINT_FRONT").'?cuenta=confirmada');
        }

        return redirect()->to(env("ENDPOINT_FRONT")."?cuenta=no-confirmada");
    }

    public function resend_email_confirm(Request $request){

        $user = User::where("email",$request->email)->first();
        if($user){
            if($user->email_verified_at){
                return response()->json(['result' => 'error-validation', 'errors' =>json_encode(["email"=>"El correo ya fue verificado."])]);
            }

            $token_email = str_replace('/', '', Hash::make(\Carbon\Carbon::now()->format("YmdHis").Str::random(10)));
            $user->token_email = $token_email;
            $user->save();
            $data = ['data'=>['email'=>$user->email,'token'=>$token_email]];

            Mail::send('mails.confirm_email',$data,function($message) use($user){
                $message->subject('Confirma tu cuenta de KLIKLER');
                $message->to($user->email);
            });

            return response()->json(['result'=>'ok','message'=>'Se ha enviado un mensaje a su correo electrónico con el código de confirmación para completar el registro.']);
        }else{

            return response()->json(['result' => 'error-validation', 'errors' =>json_encode(["email"=>"El correo no existe en nuestra base de datos."])]);
        }

    }

    public function update_data_personal(Request $request){
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'name' => 'unique:users,id,'.$user->id,
            'date_of_birth' => 'required|date',
            'document_type' => 'required',
            'document_number' => 'required|integer|max:99999999999999',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => json_encode($validator->errors())]);
        }

        $user->name = $request->name;
        $user->date_of_birth = $request->date_of_birth;
        $age = \Carbon\Carbon::now()->format('Y') - \Carbon\Carbon::parse($request->date_of_birth)->format('Y');
        $user->age = $age;
        $user->document_type = $request->document_type;
        $user->document_number = $request->document_number;
        $user->save();

        return response()->json(['result' => 'ok']);

    }

    public function update_data_contact(Request $request){
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'phone2' => 'nullable',
            'state_id' => 'integer',
            'city_id' => 'integer',
            'address' => 'nullable|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error-validation', 'errors' => json_encode($validator->errors())]);
        }

        $user->phone = $request->phone;
        $user->phone2 = $request->phone2;
        $user->state_id = $request->state_id;
        $user->city_id = $request->city_id;
        $state = State::find($request->state_id);
        if($request->state_id and $state){
            $user->state = $state->name;
        }else{
            $user->state = "";
        }
        $city = City::find($request->city_id);
        if($request->city_id and $city){
            $user->city = $city->name;
        }else{
            $user->city = "";
        }
        $user->address = $request->address;
        $user->save();

        return response()->json(['result' => 'ok']);
    }

    public function add_cities(Request $request){
        foreach( $request->states as $element){
            $provincia = new State;
            $provincia->name = $element['provincia'];
            $provincia->save();
            foreach( $element['cantones'] as $subelement){
                $canton = new City;
                $canton->name = $subelement['canton'];
                $canton->state_id = $provincia->id;
                $canton->save();
            }
        }
    }

    public function upload_image(Request $request)
    {
        if($request->type == "transaction"){
            $folder = 'transaction';
        }else if($request->type == "transaction-confirm"){
            $folder = 'transaction/confirm';
        }else{
            $folder = 'verification';
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'errors' => $validator->errors()]);
        }

        $width_min = 350;
        $width_max = 1200;

        if ($request->hasFile('file')) {

            $file = ImageIntervention::make($request->file('file')->getRealPath());

            if ($file->width() < $width_min) {
                return response()->json(["result" => "error", "message" => "La imagen debe tener un tamaño superior a " . $width_min . " píxeles."]);
            }

            $extension = $request->file('file')->getClientOriginalExtension();
            $fileName   = \Carbon\Carbon::now()->format('dmYHms').Str::random(10);

            $url_path = asset('public/images/user/'.$folder.'/' . Auth::user()->id . '/' . $fileName . '.' . $extension);
            $local_path = public_path('images/user/'.$folder.'/' . Auth::user()->id . '/' . $fileName . '.' . $extension);
            $image = new Image;

            $image->name = $fileName.$extension;
            $image->url_path = $url_path;
            $image->local_path = $local_path;
            if($request->type == 'transaction' || $request->type == 'transaction-confirm'){
                $image->transaction_id =  $request->transaction_id;
            }
            $image->user_id = Auth::user()->id;
            // if ($request->type == 'principal') {
            //     $imageAnt = Image::where('type', 'principal')->where('gallery_id', $request->gallery_id)->first();
            //     if ($imageAnt) {
            //         $this->deleteImage($imageAnt->local_path);
            //         $imageAnt->delete();
            //     }
            //     $image->type = $request->type;
            // }

            if(!$request->type){
                $image->type = "verification";
            }else{
                $image->type = $request->type;
            }
            
            $image->save();

            // make dir
            if (!File::exists('public/images')) {
                File::makeDirectory('public/images');
            }

            if (!File::exists('public/images/user')) {
                File::makeDirectory('public/images/user');
            }

            if (!File::exists('public/images/user/'.$folder)) {
                File::makeDirectory('public/images/user/'.$folder);
            }
            if (!File::exists('public/images/user/'.$folder.'/' .Auth::user()->id)) {
                File::makeDirectory('public/images/user/'.$folder.'/' .Auth::user()->id);
            }
            //move image to public/img folder
            if ($file->width() > $width_max) {
                $img = $file->resize($width_max, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $img->save('public/images/user/'.$folder.'/' . Auth::user()->id . '/' . $fileName . '.' . $extension);
            } else {
                $file->save('public/images/user/'.$folder.'/' . Auth::user()->id . '/' . $fileName . '.' . $extension);
            }

            return response()->json(["result" => "success", "message" => "Imagen subida con éxito."]);
        } else {
            return response()->json("Hubo un error al intentar subir imagen.");
        }
    }

}
