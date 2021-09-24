<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('commerce_id');
            // Nullable por si acaso no la publica el merchant
            $table->foreignId('merchant_id')->nullable();
            //Esto si el que la publica es un Empleado
            $table->foreignId('employee_id')->nullable();


            $table->string('title', 64);
            $table->string('description', 128)->nullable();
            $table->enum('status', ['active', 'suspended'])->default('active');

            $table->decimal('price', 19, 2);

            $table->unsignedMediumInteger('sales')->default(0);

            $table->string('original_image')->nullable(); // Imagen de tamaño original
            $table->string('thumbnail_image')->nullable(); // Imagen reducida, pero visible
            $table->string('avatar_image')->nullable(); // Imagen muy pequeña que va en el área de notificaciones


            $table->timestamps();
            $table->softDeletes();

            $table->foreign('commerce_id')->references('id')->on('commerces');
            $table->foreign('merchant_id')->references('id')->on('merchants');
            $table->foreign('employee_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offers');
    }
}
