<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UrlSettingsCreate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('url_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('url_id');
            $table->unsignedBigInteger('time_to_check');
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('time_checked');
            $table->timestamps();

            $table->foreign('url_id')->references('id')->on('urls');
//            $table->foreign('user_id')->references('id')->on('users');
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
