<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            // Esto solo guarda la respuesta de Wompi: https://docs.wompi.sv/metodos-api/crear-transaccion-compra
            // Esto guardará todas las peticiones, así el sistema no las muestre
            $table->id();

            $table->foreignId('purchase_id');


            $table->boolean('flag_error')->default(false);
            $table->string('id_transaccion')->nullable();
            $table->boolean('es_real')->nullable();
            $table->boolean('es_aprobada')->nullable();
            $table->string('codigo_autorizacion')->nullable();
            $table->string('mensaje')->nullable();
            $table->string('forma_pago')->nullable();
            $table->decimal('monto', 19, 3)->nullable();

            $table->string('servicio_error')->nullable();
            $table->text('mensajes_error')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('purchase_id')->references('id')->on('purchases');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
