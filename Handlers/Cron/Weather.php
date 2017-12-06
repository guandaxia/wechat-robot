<?php

/**
 * Description:
 * User: administere
 * Date: 2017/6/28
 * Time: 10:23
 */
namespace Guandaxia\Handlers\Cron;

use Carbon\Carbon;
use Hanson\Vbot\Message\Text;
use Overtrue\Pinyin\Pinyin;
use Guandaxia\Handlers\Service\Weather as WeatherService;

class Weather
{
    public static function cronHandler()
    {
        $path = realpath(vbot("config")['path'])."/weather/";
//        self::$weather->send();
        $now = Carbon::now(new \DateTimeZone("Asia/Shanghai"));

        $time = strtotime($now);
//        $today = Carbon::today()->toTimeString();
        $today = mktime(0, 0, 0, date("m"), date("d"), date("Y"));

        $setTimeName = $path. "time.json";
        if(is_file($setTimeName)){
            $setTimeArr = file_get_contents($setTimeName);
            $setTimeArr = json_decode($setTimeArr, true);
            foreach ($setTimeArr as $key=>$item) {
                $userName = $item['user_name'];
//                vbot('console')->log('天气配置信息:'. json_encode($item, JSON_UNESCAPED_UNICODE));
                //达到发送时间
                if($item['send_time'] < $today && $time > strtotime($item['set_time'])){
                    vbot('console')->log('info');
                    $city = $item['city'];
                    $weatherInfo = WeatherService::getWeather($city);

                    $pinyin = new Pinyin();
                    $city = $pinyin->permalink($city, '');

                    $yesterday = Carbon::yesterday()->toDateString();
                    $fileName = WeatherService::$weatherPath. $city. "/". $yesterday. ".json";
                    if(is_file($fileName)){
                        $oldWeather = file_get_contents($fileName);
                        $oldWeather = json_decode($oldWeather, true);
                        array_unshift($weatherInfo, $oldWeather);
                    }
                    $message = $item['city']."天气：\r\n";
                    foreach ($weatherInfo as $info) {
                        $message .=
                            "日期：".$info['date']. "\r\n".
                            "天气：". $info['weather']. "\r\n".
                            "温度：". $info['temperature']. "\r\n".
                            "风力：". $info['wind']. "\r\n".
                            "\r\n";
                    }
                    $friends = vbot('friends');
                    $userName = $friends->getUsernameByNickname($userName);
                    Text::send($userName, $message);
                    vbot('console')->log('定时发送消息:'. $time, '系统消息');
                    $setTimeArr[$key]['send_time'] = time();
                }
            }
            $setTimeArr = json_encode($setTimeArr, JSON_UNESCAPED_UNICODE);
            file_put_contents($setTimeName, $setTimeArr);
        }
    }
}