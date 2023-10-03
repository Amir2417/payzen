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
        Schema::create('stripe_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->text('name');
            $table->text('card_number');
            $table->text('expiration_date');
            $table->text('cvc_code');
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stripe_cards');
    }
};
