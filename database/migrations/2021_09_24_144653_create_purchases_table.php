<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id');
            $table->foreignId('client_id');
            $table->foreignId('commerce_id'); // Para facilitar las comprobaciones

            $table->unsignedSmallInteger('amount'); // Cantidad de platos de comida / productos

            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->decimal('total_to_pay', 19, 2);

            $table->boolean('client_completed')->default(false);
            $table->boolean('commerce_completed')->default(false);

            $table->enum('who_canceled', ['client', 'merchant', 'employee'])->nullable();
            $table->string('reason_for_cancellation')->nullable();
            // Solo para determinar quien canceló o marcó completado la orden en caso de haber sido Merchant o Employee
            $table->foreignId('merchant_id')->nullable();
            $table->foreignId('employee_id')->nullable();
            $table->dateTime('cancelled_at')->nullable();


            $table->timestamps();
            $table->softDeletes();

            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('client_id')->references('id')->on('clients');
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
        Schema::dropIfExists('purchases');
    }
}
