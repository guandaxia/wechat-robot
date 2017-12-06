<?php
/**
 * Description:
 * User: administere
 * Date: 2017/9/26
 * Time: 11:17
 */

namespace Guandaxia\Handlers\Contact;

use Hanson\Vbot\Contact\Friends;
use Hanson\Vbot\Contact\Groups;
use Hanson\Vbot\Message\Text;
use Illuminate\Support\Collection;

class WeatherContact
{
    public static function messageHandler(Collection $message, Friends $friends, Groups $groups)
    {
        if (strpos($message['content'], '天气') === 0) {
            $username = $message['from']['UserName'];
            $nickName = $message['from']['NickName'];
            $orderInfo = explode(' ', $message['content']);
            if(count($orderInfo) == 1){
                $info = <<<EOF
命令: 天气
参数:

    信息  获取设置的天气信息
    设置  设置天气信息
           格式： 地区 时间 
           如： 天气 设置 天津 7:30
    取消   取消发送天气
          
EOF;
                Text::send($username, $info);
                return;
            }

            switch ($orderInfo[1]) {
                case 'info':
                case '信息':
                    //获取设置信息
                    $result = self::getWeatherInfo($nickName);
                    break;
                case 'set':
                case '设置':
                    if(count($orderInfo) < 4){
                        $result = "请按照如下格式设置\r\n格式： 地区 时间\r\n如： 天气 设置 天津 7:30";
                    }else{
                        //设置天气
                        $setInfo = $orderInfo;
                        $result = self::setWeather($nickName, $setInfo);
                    }
                    break;
                case 'cancel':
                case '取消':
                    //取消
                    $result = self::cancelWeather($nickName);
                    break;
            }
            Text::send($username, $result);
        }
    }

    public static function getWeatherInfo($nickName)
    {
        //获取设置
        $timeName = vbot("config")['weatherPath'] . "time.json";
        if (is_file($timeName)) {
            $timeArr = file_get_contents($timeName);
            $timeArr = json_decode($timeArr, true);
            $info = "";
            $find = 0;
            foreach ($timeArr as $item) {
                if ($item['user_name'] == $nickName) {
                    $find = 1;
                    $info .= "设置时间：" . $item['set_time'] . "\r\n" .
                        "地点：" . $item['city'];
                    break;
                }
            }
            if($find == 1){
                $info .= "\n 如需修改请按照如下方式设置\n天气+设置+地名+时间的格式设置，如‘天气 设置 天津 12:00’";
                return $info;
            }
        }
        $info = "目前还没有设置信息，请按照天气+地名+时间的格式设置，如‘天气 设置 天津 12:00’";
        return $info;
    }

    public static function setWeather($nickName, $content)
    {
        //设置
        $city = $content[2];
        $time = $content[3];
        $time = str_replace([':', '：'], ':', $time);

        $setInfo = [
            'user_name' => $nickName,
            'set_time' => $time,
            'city' => $city,
            'send_time' => '',
        ];
        $timeName = vbot("config")['weatherPath'] . "time.json";
        if (is_file($timeName)) {
            $timeArr = file_get_contents($timeName);
            $timeArr = json_decode($timeArr, true);
            $find = 0;
            foreach ($timeArr as $key => $item) {
                if ($item['user_name'] == $nickName) {
                    $find = 1;
                    $timeArr[$key] = $setInfo;
                }
            }
            if ($find == 0) {
                $timeArr[] = $setInfo;
            }
        } else {
            $timeArr[] = $setInfo;
        }
        file_put_contents($timeName, json_encode($timeArr, JSON_UNESCAPED_UNICODE));

        $cityName = vbot("config")['weatherPath'] . "city.json";
        if (is_file($cityName)) {
            $cityArr = file_get_contents($cityName);
            $cityArr = json_decode($cityArr, true);
            $cityArr[] = $city;
            $cityArr = array_unique($cityArr);
        } else {
            $cityArr[] = $city;
        }
        file_put_contents($cityName, json_encode($cityArr, JSON_UNESCAPED_UNICODE));

        $info = "设置成功, 可以回复‘天气 信息’查询设置的信息";
        return $info;
    }

    public static function cancelWeather($nickName)
    {
        $timeName = vbot("config")['weatherPath'] . "time.json";
        if (is_file($timeName)) {
            $timeArr = file_get_contents($timeName);
            $timeArr = json_decode($timeArr, true);
            $find = 0;
            foreach ($timeArr as $key => $item) {
                if ($item['user_name'] == $nickName) {
                    $find = 1;
                    unset($timeArr[$key]);
                    $info = '取消成功';
                }
            }
            file_put_contents($timeName, json_encode($timeArr, JSON_UNESCAPED_UNICODE));
            if ($find == 0) {
                $info = '没有找到设置信息';
            }
        } else {
            $info = '没有找到设置信息';
        }
        return $info;
    }
}
