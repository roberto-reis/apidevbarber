<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('userfavorites', function (Blueprint $table) {
            $table->id();
            $table->integer('id_user');
            $table->integer('id_barber');
            $table->timestamps();
        });

        Schema::create('userappointments', function (Blueprint $table) {
            $table->id();
            $table->integer('id_user');
            $table->integer('id_barber');
            $table->dateTime('ap_datetime');
            $table->timestamps();
        });

        Schema::create('barbers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('avatar')->default('default.png');
            $table->float('stars')->default(0);
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->timestamps();
        });

        Schema::create('barbersphotos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_barber');
            $table->string('url');
            $table->timestamps();
        });

        Schema::create('barberreviews', function (Blueprint $table) {
            $table->id();
            $table->integer('id_barber');
            $table->float('rate');
            $table->timestamps();
        });

        Schema::create('barberservices', function (Blueprint $table) {
            $table->id();
            $table->integer('id_barber');
            $table->string('name');
            $table->float('price');
            $table->timestamps();
        });

        Schema::create('barbertestimonials', function (Blueprint $table) {
            $table->id();
            $table->integer('id_barber');
            $table->string('name');
            $table->float('rate');
            $table->string('body');
            $table->timestamps();
        });

        Schema::create('barberavailability', function (Blueprint $table) {
            $table->id();
            $table->integer('id_barber');
            $table->integer('weekday');
            $table->text('hours');
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
        Schema::dropIfExists('userfavorites');
        Schema::dropIfExists('userappointments');
        Schema::dropIfExists('barbers');
        Schema::dropIfExists('barbersphotos');
        Schema::dropIfExists('barberreviews');
        Schema::dropIfExists('barberservices');
        Schema::dropIfExists('barbertestimonials');
        Schema::dropIfExists('barberavailability');
    }
}
