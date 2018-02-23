<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Train extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'trains';

    public $incrementing = false;

    /**
     * Indicates model primary keys.
     */
    protected $primaryKey = ['valid_date', 'train_code'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable =   [
                            'valid_date',
                            'train_code',
                            'train_type',
                            'is_everyday',
                            'is_extra_train',
                            'train_class',
                            'is_allowed_no_reserved',
                            'line',
                            'line_dir',
                            'over_night_stn',
                            'is_have_cripple',
                            'is_have_package',
                            'is_have_dinning',
                            'is_have_breast_feed',
                            'is_have_bike',
                            'note',
                            'note_eng',
                            'capture_at'
                            ];

    /**
     * 新增或更新班次資料
     * @param  string  $valid_date 適用日期
     * @param  int     $train_code 班次編號
     * @param  arrays  $content    內容
     * @return boolean             有無成功
     */
    public static function updateOrCreateTrain($valid_date, $train_code, $content)
    {
        $data = [
            'valid_date' => $valid_date,
            'train_code' => $train_code,
        ];
        $data = $data + $content;

        return self::updateOrCreate($data);
    }

    public function atStation()
    {
        return $this->hasOne('App\TrainAtStation');
    }

}
