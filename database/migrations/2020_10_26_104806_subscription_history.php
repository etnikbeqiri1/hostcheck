<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SubscriptionHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('subscription_tiers_id');
            $table->string('paid_time')->default(time());
            $table->string('active_until')->nullable();
            $table->boolean('paid')->default(false);
            $table->string('method')->nullable();
            $table->timestamps();

            $table->foreign('subscription_tiers_id')->references('id')->on('subscription_tiers');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
