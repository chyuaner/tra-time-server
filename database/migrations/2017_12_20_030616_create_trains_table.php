<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trains', function (Blueprint $table) {
            $table->date('valid_date')->comment('適用日期');
            $table->integer('train_code')->comment('車次');
            $table->integer('train_type')->comment('火車狀態（0：常態列車,1：臨時,2：團體列車,3春節加開車）');
            $table->boolean('is_everyday')->comment('每日行駛');
            $table->boolean('is_extra_train')->comment('加班車');
            $table->integer('train_class')->comment('列車種類');
            $table->boolean('is_allowed_no_reserved')->comment('有無提供無座票');
            $table->integer('line')->comment('行駛 山、海線或無經過 1（0（不經過山海線）,1(山),2(海)）');
            $table->integer('line_dir')->comment('列車行駛方向 1 (0=順時針，1=逆時針)');
            $table->integer('over_night_stn')->comment('跨夜車站代號（0為不跨日，有資料代表為跨夜車，ETime為次日時間。）');
            $table->boolean('is_have_cripple')->comment('殘障車');
            $table->boolean('is_have_package')->comment('辦理托運');
            $table->boolean('is_have_dinning')->comment('餐車');
            $table->boolean('is_have_breast_feed')->comment('設有哺(集)乳室車廂');
            $table->boolean('is_have_bike')->comment('腳踏車');
            $table->string('note')->comment('備註');
            $table->string('note_eng')->comment('備註(英文)');
            $table->timestamp('capture_at')->comment('資料爬取時間');
            $table->timestamps();
            $table->primary(['valid_date', 'train_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trains');
    }
}
