<?php

namespace App\Console\Commands;

use App\Train;
use App\TrainAtStation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Chumper\Zipper\Zipper;

class TraTrains extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tra:trains
                            {date? : 輸入欲取得日期(YYYY-MM-DD)}
                            {--d|day=1 : 輸入取得日的後續幾天範圍內也一起取}
                            {--no-remove : 不移除暫存檔案}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '從台鐵的政府資料開放平台取得班次資料';

    protected $base_url = 'http://163.29.3.98/json/'; // TODO: 挪到.env處理
    protected $default_days = 14; // TODO: 挪到.env處理
    protected $max_days = 60; // TODO: 挪到.env處理
    protected $dirname = 'storage/app/fetch/';
    protected $capture_at;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 取得輸入參數資料
        $input_date = $this->argument('date');
        $input_day = $this->option('day');
        $input_noRemoveTempFile = $this->option('no-remove');

        // 標題
        $this->info('取得台鐵班次資料，從政府資料開放平台');

        // 若有輸入欲爬取日期
        if (isset($input_date)) {
            $date = Carbon::parse($input_date);

            // 若輸入的日期是在今天以前的話
            if ($date < Carbon::today()) {
                $this->error('上游資料沒有今日之前的資料喔！');
                return false;
            }
        }
        // 若沒輸入欲爬取日期，就以今日日期為主
        else {
            $date = Carbon::today();
        }
        // 計算實際可擷取的天數最大值
        $enable_max_day =
            $this->max_days - ($date->diffInDays(Carbon::today()));

        // 處理結尾範圍日期
        if ($input_day>$enable_max_day) {
            $this->error('上游資料沒有太久遠超過'.$enable_max_day.'天啦～'.
                         '那接下來就擷取到'.$enable_max_day.'天內的吧！');
            $input_day = $enable_max_day;
        }
        $end_date = $date->copy()->addDays($input_day-1);


        // 顯示資訊
        $this->line('索取範圍:'.
                    $date->toDateString().' ~ '.$end_date->toDateString());

        // 處理要爬取的網址陣列
        $dates = array();
        for ($i=0; $i<$input_day; $i++) {
            $the_date = $date->copy()->addDays($i);
            array_push($dates, $the_date);
        }

        $bar = $this->output->createProgressBar(count($dates));
        $bar->setFormat(
            ' %current%/%max% [%bar%] %percent:3s%% %message% %filename%');
        $bar->start();
        foreach ($dates as $the_date) {
            // 設定進度條文字
            $bar->setMessage(':');
            $message = $this->base_url.$the_date->format('Ymd').'.zip';
            $bar->setMessage($message, 'filename');

            // 執行爬蟲動作
            $this->downloadTrainInfoFile($the_date);
            $this->capture_at = Carbon::now()->toDateTimeString();
            $this->saveTrainInfo($the_date);
            if(!$input_noRemoveTempFile) {
                $this->removeTrainInfoFile($the_date);
            }

            // 進度條步驟+1
            $bar->advance();
        }
        $bar->finish();
    }

    protected function getDateString($the_date)
    {
        return $the_date->format('Ymd');
    }

    protected function getZipUrl($the_date)
    {
        // 處理下載網址 $file_zip_url
        $file_zip_name = $this->getDateString($the_date).'.zip';
        $file_zip_url = $this->base_url.$file_zip_name;

        return $file_zip_url;
    }

    protected function downloadTrainInfoFile($the_date)
    {
        // 轉成日期字串
        $date_string = $this->getDateString($the_date);

        // 下載
        // TODO: 404例外處理
        if (!is_dir($this->dirname)) {mkdir($this->dirname, 0755, true);}
        $zip_file = fopen($this->dirname.$date_string.'.zip','w');
        $client = new Client();
        $response = $client->get($this->getZipUrl($the_date));
        $zip_content = $response->getBody();
        fwrite($zip_file,$zip_content);
        fclose($zip_file);

        // 解壓縮
        $zipper = new Zipper;
        $filename = $zipper->make($this->dirname.$date_string.'.zip')
                           ->extractTo($this->dirname);

    }

    protected function charToBool($char)
    {
        if ($char == 'Y')
        {
            return true;
        }
        else {
            return false;
        }
    }

    protected function noteToAllowedNoReserved($note_string)
    {
        if ( strpos($note_string, '不發售無座票') !== false
          || strpos($note_string, '不發售團體票及無座票') !== false
        )
        {
            return false;
        }
        else {
            return true;
        }
    }

    protected function noteToIsEveryday($note_string)
    {
        if (strpos($note_string, '每日行駛') !== false) {
            return true;
        }
        else {
            return false;
        }
    }

    protected function noteToIsExtraTrain($note_string)
    {
        return false;
    }

    protected function saveTrainInfo($the_date)
    {
        // 處理所需資料成 $orig_content
        $date_string = $this->getDateString($the_date);
        $file_content = file_get_contents($this->dirname.$date_string.'.json');
        $orig_content = json_decode($file_content, true);

        // 爬取資料
        foreach ($orig_content['TrainInfos'] as $orig_the_train) {
            // 當班次的詳細資訊擷取
            $capture_at = Carbon::now()->toDateTimeString();
            $valid_date = $the_date->toDateString();
            $train_type          = (int)$orig_the_train['Type'];
            $train_code          = (int)$orig_the_train['Train'];
            $is_have_breast_feed = $this->charToBool($orig_the_train['BreastFeed']);
            $is_have_package     = $this->charToBool($orig_the_train['Package']);
            $over_night_stn      = (int)$orig_the_train['OverNightStn'];
            $line_dir            = (int)$orig_the_train['LineDir'];
            $line                = (int)$orig_the_train['Line'];
            $is_have_dinning     = $this->charToBool($orig_the_train['Dinning']);
            $is_have_cripple     = $this->charToBool($orig_the_train['Cripple']);
            $train_class         = (int)$orig_the_train['CarClass'];
            $is_have_bike        = $this->charToBool($orig_the_train['Bike']);
            $note                = $orig_the_train['Note'];
            $note_eng            = $orig_the_train['NoteEng'];
            $is_everyday         = $this->noteToIsEveryday($note);
            $is_extra_train      = $this->noteToIsExtraTrain($note);
            // $is_everyday         = $orig_the_train['Everyday'];
            // $is_extra_train      = $orig_the_train['ExtraTrain'];
            $is_allowed_no_reserved = $this->noteToAllowedNoReserved($note);

            // 寫入進資料庫
            $db_train = Train::updateOrCreateTrain($valid_date, $train_code, [
                'train_type'             => $train_type,
                'is_everyday'            => $is_everyday,
                'is_extra_train'         => $is_extra_train,
                'train_class'            => $train_class,
                'is_allowed_no_reserved' => $is_allowed_no_reserved,
                'line'                   => $line,
                'line_dir'               => $line_dir,
                'over_night_stn'         => $over_night_stn,
                'is_have_cripple'        => $is_have_cripple,
                'is_have_package'        => $is_have_package,
                'is_have_dinning'        => $is_have_dinning,
                'is_have_breast_feed'    => $is_have_breast_feed,
                'is_have_bike'           => $is_have_bike,
                'note'                   => $note,
                'note_eng'               => $note_eng,
                'capture_at'             => $capture_at
            ]);

            $time_infos = array();
            $first_dep_time;
            foreach ($orig_the_train['TimeInfos'] as $orig_the_time_info) {
                $station_code = $orig_the_time_info['Station'];
                $order    = (int)$orig_the_time_info['Order'];
                $arr_time = Carbon::parse(
                    $the_date->toDateString().' '.$orig_the_time_info['ArrTime']);
                $dep_time = Carbon::parse(
                    $the_date->toDateString().' '.$orig_the_time_info['DepTime']);

                // 修正跨日的日期資料
                if($order <= 1) { $first_dep_time = $dep_time; }
                else {
                    if($arr_time < $first_dep_time) { $arr_time->addDay(); }
                    if($dep_time < $first_dep_time) { $dep_time->addDay(); }
                }

                // 寫入進資料庫
                $db_info = TrainAtStation::updateOrCreateTrainAt($valid_date, $train_code, $station_code, [
                    'order'    => $order,
                    'arr_time' => $arr_time,
                    'dep_time' => $dep_time,
                    'capture_at' => $capture_at
                ]);
            }

        }
    }

    protected function removeTrainInfoFile($the_date)
    {
        // 轉成日期字串
        $date_string = $this->getDateString($the_date);

        // 刪除檔案
        unlink($this->dirname.$date_string.'.zip');
        unlink($this->dirname.$date_string.'.json');
    }
}
