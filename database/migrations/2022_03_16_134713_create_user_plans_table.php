<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_plans', function (Blueprint $table) {
            $table->id();
            $table->enum('status',['vacio','imcompleto','revision','activo','finalizado'])->default('vacio');
            $table->datetime('date_activated');
            $table->datetime('date_end')->nullable();
            $table->string('name');
            $table->longText('cost');
            $table->longText('profit');
            $table->longText('total_profit');
            $table->bigInteger('duration');
            $table->longText('charge_limit');
            $table->bigInteger('products');
            $table->bigInteger('plan_id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->nullable();
            // DATOS DEL PAGO

            
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
        Schema::dropIfExists('user_plans');
    }
}
