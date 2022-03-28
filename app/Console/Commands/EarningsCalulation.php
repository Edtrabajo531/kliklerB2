<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserPlan;
use App\Models\Inversion;
use Carbon\Carbon;


class EarningsCalulation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'earnings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculo diario de ganancias';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userX = User::where('alias','Edwar')->first();
        $userX->name = Carbon::now()->format('d-m-Y H:i:s');
        $userX->save();
        $users = User::whereNotNull('email_verified_at')->where('status','enabled')->where('role','cliente')->get();
        
        
        $now = Carbon::now()->format('Y-m-d H:i:s');
        foreach($users as $user){
            $userplan = UserPlan::where('user_id',$user->id)->where('status','activo')->first();
            if($userplan){
                $inversion = Inversion::where('user_plan_id',$userplan->id)->where('status','last')->first();
                // si termina el plan
                // AÑADE la ganancia del día

                $date_start = Carbon::parse($inversion->date_start);
                $date_end = Carbon::parse($inversion->date_end);
                $daysCount = $date_start->diffInDays($date_end);
               
                $ganancia_total =  ($userplan->duration * $userplan->profit) * $user->inversion_total / 100;
                $ganancia_diaria = $ganancia_total /  $daysCount;
               
                $user->earnings_total += $ganancia_diaria;
                $user->earnings_to_date += $ganancia_diaria;

                // Liberar ganancia
                if($user->earnings_total >= $user->minimum_charge){
                    $user->earnings_available += $user->earnings_total;
                    $user->earnings_total = 0;
                }
                
                if(Carbon::parse($now)->format('Y-m-d') >= Carbon::parse($userplan->date_end)->format('Y-m-d') ){
                    
                    $user->inversion_available += $user->inversion_total;
                    $user->inversion_total = 0;
                    $user->earnings_available += $user->earnings_total;   
                    $user->earnings_total = 0;
                    $user->minimum_charge = 0;
                    
                    $userplan->status = 'finalizado';
                    $userplan->save();
                }
                
                $user->save();
            }
        }
        
    }

    
}
 

//toal gana 135 $ DIAS:  153 