<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statements', function (Blueprint $table) {
            $table->uuid('id')->unique();
            $table->uuid('wallet_id');
            $table->uuid('transaction_id');
            $table->integer('value');
            $table->integer('old_balance')->nullable();
            $table->integer('new_balance')->nullable();
            $table->string('status', 20);
            $table->integer('type');
            $table->timestamps();

            $table->foreign('wallet_id')
              ->references('id')
              ->on('wallets');
            $table->foreign('transaction_id')
              ->references('id')
              ->on('transactions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statements');
    }
};
