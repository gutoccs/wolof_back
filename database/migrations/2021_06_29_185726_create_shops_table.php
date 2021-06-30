<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();

            $table->string('id_public', 24)->unique();

            $table->string('trade_name', 128)->unique();
            $table->string('legal_name', 128)->unique()->nullable();
            $table->string('tax_identification_number', 64)->unique()->nullable();
            $table->string('short_description')->nullable();
            $table->string('slogan')->nullable();
            $table->string('original_profile_image')->nullable();
            $table->string('thumbnail_profile_image')->nullable();
            $table->string('avatar_profile_image')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shops');
    }
}
