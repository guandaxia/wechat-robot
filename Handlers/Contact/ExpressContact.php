<?php
/**
 * Description:
 * User: administere
 * Date: 2017/11/24
 * Time: 11:17
 */

namespace Guandaxia\Handlers\Contact;

use Guandaxia\Handlers\Service\Express;
use Hanson\Vbot\Contact\Friends;
use Hanson\Vbot\Contact\Groups;
use Hanson\Vbot\Message\Text;
use Illuminate\Support\Collection;

class ExpressContact
{
    public static function messageHandler(Collection $message, Friends $friends, Groups $groups)
    {
        $username = $message['from']['UserName'];
        $nickName = $message['from']['NickName'];
        if(preg_match('/^\d{9,}/', $message['content'], $match)){
            vbot('console')->log('express');
            $message = $match[0];

            Text::send($username, '🔎快递信息正在查询中……');
            $express = new Express();
            $result = $express->query($message);
            Text::send($username, $result);

            //保存查询单号，方便定时查询
            $express->saveNumber($nickName, $message);
        }
    }

}