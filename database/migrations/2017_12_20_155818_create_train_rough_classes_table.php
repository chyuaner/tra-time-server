<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainRoughClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_rough_classes', function (Blueprint $table) {
            $table->increments('train_rough_class_id')->comment('大略車種ID');
            $table->integer('order')->comment('停靠流水排序編號');
            $table->string('name')->comment('車種名');
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
        Schema::dropIfExists('train_rough_classes');
    }
}
