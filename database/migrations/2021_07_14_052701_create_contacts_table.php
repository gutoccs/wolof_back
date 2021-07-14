<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('commerce_id');

            $table->string('web', 128)->nullable();
            $table->string('whatsapp', 32)->nullable();
            $table->string('instagram', 128)->nullable();
            $table->string('facebook', 128)->nullable();
            $table->string('twitter', 128)->nullable();
            $table->string('linkedin', 128)->nullable();
            $table->string('youtube', 128)->nullable();
            $table->string('tiktok', 128)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('commerce_id')->references('id')->on('commerces');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contacts');
    }
}
