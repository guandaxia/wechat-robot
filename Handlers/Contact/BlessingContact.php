<?php
/**
 * Description:
 * User: guansixu
 * Date: 2018/2/12
 * Time: 16:45
 */

namespace Guandaxia\Handlers\Contact;

use Hanson\Vbot\Contact\Friends;
use Hanson\Vbot\Contact\Groups;
use Hanson\Vbot\Message\Text;
use Illuminate\Support\Collection;

class BlessingContact
{
    public static function messageHandler(Collection $message, Friends $friends, Groups $groups)
    {
        if ($message['content'] == '祝福') {
//            foreach ($friends as $friend) {
//                $name = $friend['RemarkName'] ?: $friend['NickName'];
//                $userName = $friend['UserName'];
//            }
            $file = __DIR__ . "/../../tmp/zhufu.json";
            $blessArr = file_get_contents($file);
            $blessArr = json_decode($blessArr, true);
            print_r($blessArr);
            $bless = $blessArr[rand(0, count($blessArr) -1)];

            Text::send($message['from']['UserName'], $bless);
        }
    }
}