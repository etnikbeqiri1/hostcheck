<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UrlsCreate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('urls', function (Blueprint $table) {
            $table->id();
            $table->string('site');
            $table->unsignedBigInteger('last_response');
            $table->unsignedBigInteger('time_checked');
            $table->boolean('need_check')->default(false);
            $table->timestamps();

            /*$table->foreign('subscription_history_id')->references('id')->on('subscription_history');
            $table->foreign('user_id')->references('id')->on('users');*/
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
