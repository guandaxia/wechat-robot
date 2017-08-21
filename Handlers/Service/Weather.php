<?php
/**
 * Description: 自动发送天气预报
 * User: guansixu
 * Date: 2017/6/15
 * Time: 11:19
 */
namespace Guandaxia\Handlers\Service;

use Carbon\Carbon;
use Guandaxia\lib\Db;
use Hanson\Vbot\Message\Text;
use Overtrue\Pinyin\Pinyin;

class Weather
{
    private $isSend = 0;
    public static $weatherPath = __DIR__ ."/../../tmp/weather/";
    public function __construct()
    {

    }

    public static function getWeather($city)
    {
        if(empty($city)){
            return false;
        }
        if(is_array($city)){
            //多个城市用|隔开
            $city = implode("|", $city);
        }
        $url = "http://api.map.baidu.com/telematics/v3/weather?";
        $query = [
            'ak'        =>  '2adbsQunTBLLt7qb2MLCCdtH2ruPVyci',
            'location'  => $city,
            'output'    =>  "json",
        ];
        $query = http_build_query($query);
        $url = $url.$query;

        $result = vbot('http')->get($url);
        $result = json_decode($result, true);
        if($result['error'] == 0){

            $info = $result['results'];
            $pinyin = new Pinyin();
            $weatherInfo = [];
            foreach ($info as $item) {
                $cityName = $pinyin->permalink($item['currentCity'], '');
                $fileName = self::$weatherPath. $cityName . "/". $result['date'].".json";
                if(!is_dir(dirname($fileName))){
                    mkdir(dirname($fileName), 0777, true);
                }
                file_put_contents($fileName, json_encode($item['weather_data'][0], JSON_UNESCAPED_UNICODE));

            }
            return $info[0]['weather_data'];
        }
    }
}