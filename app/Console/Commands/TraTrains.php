<?php

namespace App\Console\Commands;

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

    protected function saveTrainInfo($the_date)
    {
        // 處理所需資料成 $content
        $date_string = $this->getDateString($the_date);
        $file_content = file_get_contents($this->dirname.$date_string.'.json');
        $content = json_decode($file_content);


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
