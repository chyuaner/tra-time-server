<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCitysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('citys', function (Blueprint $table) {
            $table->increments('city_code');
            $table->string('name')->comment('名稱');
            $table->string('name_eng')->comment('英文名稱');
            $table->string('name_jp')->comment('日文名稱');
            $table->string('name_kr')->comment('韓文名稱');
            $table->string('name_sc')->comment('簡體名稱');
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
        Schema::dropIfExists('citys');
    }
}
