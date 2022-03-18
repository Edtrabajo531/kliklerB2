<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('role',['administrador-p','administrador','asistente','cliente']);
            $table->enum('status',['enabled','disabled'])->default('enabled');
            $table->string('alias')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('token_email')->nullable();
            // $table->string('nationality')->nullable();
            $table->string('document_type')->nullable();
           
            $table->bigInteger('document_number')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->integer('age')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone2')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->foreign('state_id')->references('id')->on('states');
            $table->unsignedBigInteger('city_id')->nullable();
            $table->foreign('city_id')->references('id')->on('cities');
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('municipality')->nullable();
            $table->string('address')->nullable();
            $table->string('imagen_de_perfil')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('user_verified_at')->nullable();
            $table->enum('user_verified',[0,'waiting',1,'reject'])->default(0);
            $table->longText('token_password')->nullable();
            $table->timestamp('date_token_password')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
