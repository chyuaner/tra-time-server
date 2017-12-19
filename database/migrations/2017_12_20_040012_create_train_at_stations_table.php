<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainAtStationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_at_stations', function (Blueprint $table) {
          $table->date('valid_date')->comment('適用日期');
          $table->integer('train_code')->comment('車次');
          $table->integer('station_code')->comment('停靠站');
          $table->integer('order')->comment('停靠流水排序編號');
          $table->dateTime('arr_time')->comment('抵達時間');
          $table->dateTime('dep_time')->comment('發車時間');
          $table->timestamp('capture_at')->comment('資料爬取時間');
          $table->timestamps();
          $table->primary(['valid_date', 'train_code','station_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('train_at_stations');
    }
}
