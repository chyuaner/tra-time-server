<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('train_classes', function (Blueprint $table) {
            $table->increments('train_class_code');
            $table->integer('train_rough_class_id')->comment('大略車種ID');
            $table->string('name')->comment('車種名');
            $table->boolean('is_reserved')->comment('是否為對號座');
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
        Schema::dropIfExists('train_classes');
    }
}
