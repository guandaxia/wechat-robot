<?php
/**
 * Description:
 * User: administere
 * Date: 2017/7/28
 * Time: 15:31
 */

namespace Guandaxia\Handlers\Cron;


use Hanson\Vbot\Message\File;
use Hanson\Vbot\Message\Text;

class DaliyReport
{
    public static function cronHandler()
    {
        if(date("H") == 16 && date("i") < 50){
            // 好友实例
            $friends = vbot('friends');
            $username = $friends->getUsernameByNickname('管思旭', $blur = false);

            $path = "/root/code/";
            $filename = $path. sprintf("管思旭的项目日报%s年%s月%s日.xlsx", date('Y'), date('m'), date('d'));
            iconv('UTF-8', 'UTF-8', $filename);
            echo filesize(iconv('UTF-8', 'UTF-8', $filename));
            File::send($username, $filename);
        }

    }
}