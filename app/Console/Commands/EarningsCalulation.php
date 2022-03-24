<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\models\User;
use App\models\Userplan;
use App\models\Inversion;
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
        $users = User::where('status','enabled')->get();
        $now = Carbon::now()->format('Y-m-d H:i:s');
        foreach($users as $user){
            $userplan = Userplan::where('user_id',$user->id)->where('status','activo')->first();
            if($userplan){
                $inversion = Inversion::where('user_plan_id',$userplan->id)->where('status','activo')->first();
                // si termino el plan
                if($now > $inversion->date_end){
                    $userplan->status = 'finalizado';
                    $inversion->status = 'completed';
                    $userplan->save();
                    $inversion->save();
                    continue;
                }
                
                $daysMonth = Carbon::parse($now)->addMonth();
                $daysMonth = $daysMonth->endOfMonth()->format('d');
                $user->name = $daysMonth;
                $user->save();
                $porcentE = $userplan->minimum_charge /  $daysMonth;
                $inversion->total_profit = $inversion->total_profit + floatval($porcentE);

                // verifica si puede liberar dinero
                $inversion->save();
            }
        }
        
    }
}
