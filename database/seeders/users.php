<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class users extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'alias'=>'Admin',
            'name'=>'Administrador principal',
            'email'=>'Administrador@gmail.com',
            'password'=>bcrypt('12345678'),
            "email_verified_at"=>'2022-02-02 00:00:00',
            'role'=>'administrador-p',
            'token_email'=>Null,
        ]);
    }
}
