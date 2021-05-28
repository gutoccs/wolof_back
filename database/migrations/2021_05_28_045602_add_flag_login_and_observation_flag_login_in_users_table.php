<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFlagLoginAndObservationFlagLoginInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('flag_login')->default(true); // Indica si el usuario se puede autenticar
            $table->string('observation_flag_login')->nullable(); // Indica el motivo por el cual no puede autenticarse
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['flag_login', 'observation_flag_login']);
        });
    }
}
