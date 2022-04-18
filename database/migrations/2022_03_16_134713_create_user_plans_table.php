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
            $table->enum('status',['vacio','imcompleto','revision','activo','finalizado','rechazado'])->default('vacio');
            $table->datetime('date_request')->nullable();
            $table->datetime('date_activated')->nullable();
            $table->datetime('date_end')->nullable();
            $table->string('name');
            $table->double('cost');
            $table->double('profit');
            $table->bigInteger('duration');
            $table->bigInteger('products');
            $table->bigInteger('plan_id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->nullable();

           
            $table->double('inversion')->nullable();
            $table->double('total_profit')->nullable();
            $table->double('minimum_charge')->nullable();
            $table->double('minimum_charge_invertion')->nullable();
            // DATOS DEL PAGO
            $table->double("pay_in_dollars")->nullable();
            $table->double("total_pay_dollars")->nullable();
            $table->double("pay_in_btc")->nullable();
            $table->double("license")->nullable();
            $table->double("total_pay")->nullable();
            // BANCO
            $table->bigInteger('bank_id')->nullable();
            $table->string('nameBank')->nullable();
            $table->string('holderBank')->nullable();
            $table->bigInteger('identificationBank')->nullable();
            $table->string('typeBank')->nullable();
            $table->longText('numberAccountBank')->nullable();
            // WALLET
            $table->bigInteger('wallet_id')->nullable();
            $table->string('nameWallet')->nullable();
            $table->longText('addressWallet')->nullable();
            $table->string('coinWallet')->default('Bitcoin');
            $table->longText('linkWallet')->nullable();
            $table->longText('observations')->nullable();
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
