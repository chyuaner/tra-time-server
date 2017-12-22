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

    public function atStation()
    {
        return $this->hasOne('App\TrainAtStation');
    }

}
