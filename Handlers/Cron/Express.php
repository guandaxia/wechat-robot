<?php
/**
 * Description:
 * User: guansixu
 * Date: 2017/12/4
 * Time: 下午2:36
 */

namespace Guandaxia\Handlers\Cron;

use Hanson\Vbot\Message\Text;

class Express
{
    public static $runtime = '';
    public static function cronHandler()
    {
        if(self::$runtime  < (time() - 3600)){
            vbot('console')->log('定时查询快递');
            self::$runtime = time();
            $express = new \Guandaxia\Handlers\Service\Express();
            $expressInfo = $express->getExpressList();

            foreach ($expressInfo as $item) {
                $friends = vbot('friends');
                $userName = $friends->getUsernameByNickname($item['userName']);
                Text::send($userName, $item['message']);
                vbot('console')->log('快递信息发送成功');
            }
        }
    }
}