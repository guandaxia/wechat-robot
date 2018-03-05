<?php
/**
 * Description:
 * User: administere
 * Date: 2017/9/26
 * Time: 10:00
 */

namespace Guandaxia\Handlers\Contact;


use Hanson\Vbot\Contact\Friends;
use Hanson\Vbot\Contact\Groups;
use Illuminate\Support\Collection;

class CheckUserContact
{
    public static function messageHandler(Collection $message, Friends $friends, Groups $groups)
    {
        if ($message['content'] == '检测好友') {
            $userName = [];
            foreach ($friends as $friend) {
                $userName[] = $friend['UserName'];
            }
//            $userName[0] = $friends->getUsernameByNickname('管大侠');
//            $userName[1] = $friends->getUsernameByNickname('飞飞');
//            var_dump($userName);
            //创建群组
            $result = $groups->create($userName);
            $groupUsername = $result['UserName'];
            $groups->deleteMember($groupUsername, $userName);

        }


    }
}