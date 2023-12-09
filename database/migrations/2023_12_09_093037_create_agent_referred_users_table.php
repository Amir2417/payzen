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
        Schema::create('agent_referred_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('refer_agent_id')->comment("Who own the refer or parent");
            $table->unsignedBigInteger('new_agent_id')->comment("who use a referral id when registering");
            $table->timestamps();

            $table->foreign('new_agent_id')->references('id')->on('agents')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('refer_agent_id')->references('id')->on('agents')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agent_referred_users');
    }
};
