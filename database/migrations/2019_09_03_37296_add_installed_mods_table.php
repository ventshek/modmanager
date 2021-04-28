<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInstalledModsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('installed_mods', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('server_id');
            $table->integer('mod_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('installed_mods');
    }
}
