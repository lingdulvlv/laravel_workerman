<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class addjoin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'join:add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '记录某个用户进入某房间';

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
        // \Cache::forget('uid_company');
        $uid_company = \Cache::get('uid_company');
        $res_record = DB::table('record')->where('read',2)->orderBy('create_time','desc')->limit(1)->first();
        $time = 0;
        $data_add = [];
        if(!empty($res_record)){
            $time = $res_record->create_time;
        }
        foreach($uid_company as $key => $val){
            if($val['create_time']>$time){
                $data_add[] = $val;
            }else{
                // unset($record[$key]);
            }
        }
        if(!empty($data_add)){
            DB::table('record')->insert($data_add);
        }
    }
}
