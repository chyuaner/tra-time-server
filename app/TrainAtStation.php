<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TrainAtStation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'train_at_stations';

    public $incrementing = false;

    /**
     * Indicates model primary keys.
     */
    protected $primaryKey = ['valid_date', 'train_code', 'station_code'];

    /**
     * 新增或更新班次資料
     * @param  string  $valid_date 適用日期
     * @param  int     $train_code 班次編號
     * @param  arrays  $content    內容
     * @return boolean             有無成功
     */
    public static function updateOrCreateTrainAt($valid_date, $train_code, $content)
    {
        $count = self::where('valid_date', $valid_date)
                     ->where('train_code', $train_code)
                     ->count();

        if($count) {
            return self::where('valid_date', $valid_date)
                       ->where('train_code', $train_code)
                       ->update($content);
        }
        else
        {
            $attr = [
                'valid_date' => $valid_date,
                'train_code' => $train_code
            ];

            unset($content['valid_date']);
            unset($content['train_code']);
            $attr = $attr + $content;

            return self::create($attr);
        }
    }

}
