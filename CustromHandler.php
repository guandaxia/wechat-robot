<?php

/**
 * Description: 定时任务处理
 * User: guansixu
 * Date: 2017/7/3
 * Time: 12:46
 */
namespace Guandaxia;

use Guandaxia\Handlers\Cron\Bike;
use Guandaxia\Handlers\Cron\DaliyReport;
// use Guandaxia\Handlers\Cron\Express;
use Guandaxia\Handlers\Cron\Weather;

class CustromHandler
{
    public static function custromHandler()
    {
        //自行车
       // Bike::cronHandler();
        //天气
        Weather::cronHandler();
        //日报
        //DaliyReport::cronHandler();
        //快递
//        Express::cronHandler();
    }
}