<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStationNamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('station_names', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('station_code')->comment('站ID編號');
            $table->string('name')->comment('名稱');
            $table->string('lang')->comment('語言');
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
        Schema::dropIfExists('station_names');
    }
}
