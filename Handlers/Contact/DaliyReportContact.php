<?php
/**
 * Description:
 * User: administere
 * Date: 2017/7/28
 * Time: 15:09
 */

namespace Guandaxia\Handlers\Contact;

use Hanson\Vbot\Message\Text;
use Illuminate\Support\Collection;

class DaliyReportContact
{
    private static $daliyReportInfo = [];

    public static function messageHandler(Collection $message){
        $username = $message['from']['UserName'];
        if ($message['content'] == '日报'){
            self::$daliyReportInfo[$username] = [
                'name'  =>  $message['from']['NickName'],
                'step'  =>  1,
                'create_time'   => time(),
            ];
            Text::send($username, "开始日报\n请输入今天的工作内容：");
            return;
        }
        if(empty(self::$daliyReportInfo[$username])){
            return;
        }

        $step = self::$daliyReportInfo[$username]['step'];
        $createTime = self::$daliyReportInfo[$username]['create_time'];
        if($step > 2 || $step < 1 || $createTime > (time() + 600)){
            self::$daliyReportInfo[$username] = [];
            return;
        }

        if($message['content'] == '修改'){
            self::$daliyReportInfo[$username] = [
                'step'  =>  1,
                'create_time'   => time(),
            ];
            Text::send($username, '请重新输入今天工作内容：');
            return;
        }
        switch ($step) {
            case 1:
                $content = $message['content'];
                self::$daliyReportInfo[$username]['contnet'] = $content;
                self::$daliyReportInfo[$username]['step'] = 2;
                Text::send($username, "请输入明天的工作计划”");
                break;
            case 2:
                $content = $message['content'];

                $path = realpath(vbot("config")['path'])."/daliy-report/";
                $daliyContent = self::$daliyReportInfo[$username]['contnet']. "\n". $content;

                $filename = $path."content.txt";
                file_put_contents($filename, $daliyContent);
                self::$daliyReportInfo[$username] = [];
                Text::send($username, "日报完成");
                break;
        }
        return;
    }
}