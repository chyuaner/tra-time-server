<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stations', function (Blueprint $table) {
            $table->increments('station_code')->comment('站ID編號');
            $table->integer('city_code')->comment('所屬城市');
            $table->string('name')->comment('中文主要站名');
            $table->string('name_eng')->comment('英文主要站名');
            $table->integer('station_order')->comment('車站排序');
            $table->integer('station_class')->comment('站等級');
            $table->string('tel')->comment('電話');
            $table->string('address')->comment('地址');
            $table->string('address_eng')->comment('英文地址');
            $table->timestamp('capture_at')->comment('資料爬取時間');
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
        Schema::dropIfExists('stations');
    }
}
