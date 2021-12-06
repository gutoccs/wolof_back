<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWompisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wompis', function (Blueprint $table) {
            $table->id();

            $table->text('access_token');
            $table->string('expires_in');
            $table->string('token_type');
            $table->string('scope');

            $table->dateTime('calculated_expiration');

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
        Schema::dropIfExists('wompis');
    }
}
