<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfileImageInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('original_profile_image')->nullable(); // Imagen de tamaño original
            $table->string('thumbnail_profile_image')->nullable(); // Imagen reducida, pero visible
            $table->string('avatar_profile_image')->nullable(); // Imagen muy pequeña que va en el área de notificaciones
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
            $table->dropColumn(['original_profile_image', 'thumbnail_profile_image', 'avatar_profile_image']);
        });
    }
}
