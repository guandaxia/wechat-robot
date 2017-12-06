<?php

namespace Guandaxia\Handlers\Type;

use Carbon\Carbon;
use Guandaxia\Handlers\Service\OfoBike;
use Guandaxia\Handlers\Service\Password;
use Guandaxia\Handlers\Service\Train;
use Hanson\Vbot\Contact\Friends;
use Hanson\Vbot\Contact\Groups;
use Hanson\Vbot\Message\Image;
use Hanson\Vbot\Message\Text;
use Hanson\Vbot\Support\File;
use Illuminate\Support\Collection;

class TextType
{
    private static $ofoLogin = [];
    private static $ofoRepair;

    public static function messageHandler(Collection $message, Friends $friends, Groups $groups)
    {
        $path = realpath(vbot("config")['path'])."/";
        $username = $message['from']['UserName'];
        $isLogin = isset(static::$ofoLogin[$username]);

        if ($message['type'] === 'text') {
            if ($message['content'] === 'time') {
                $datetime = Carbon::parse(vbot('config')->get('server.time'));
                Text::send($message['from']['UserName'], 'Running:' . $datetime->diffForHumans(Carbon::now()));
            }
            elseif ($message['content'] === '图片') {
                $filename = realpath(vbot("config")['path']) . "/ofo/captcha.jpg";
                vbot('console')->log($filename);
                Image::send($username, $filename);
                Text::send($username, '图片发送成功');
            } elseif ($message['content'] === '拉我') {
                $username = $groups->getUsernameByNickname('Vbot 体验群');
                $groups->addMember($username, $message['from']['UserName']);
            } elseif ($message['content'] === '叫我') {
                $username = $friends->getUsernameByNickname('HanSon');
                Text::send($username, '主人');
            } elseif ($message['content'] === '头像') {
                $avatar = $friends->getAvatar($message['from']['UserName']);
                File::saveTo(vbot('config')['user_path'] . 'avatar/' . $message['from']['UserName'] . '.jpg', $avatar);
            } elseif ($message['fromType'] === 'Group' && $message['isAt']) {
               // Text::send($message['from']['UserName'], static::reply($message['pure'], $message['from']['UserName']));
            } elseif ($message['fromType'] === 'Friend') {
                //Text::send($message['from']['UserName'], static::reply($message['content'], $message['from']['UserName']));
            } else {
                //Text::send($message['from']['UserName'], static::reply($message['content'], $message['from']['UserName']));
            }
        }
    }

    private static function reply($content, $id)
    {
        try {
            $result = vbot('http')->post('http://www.tuling123.com/openapi/api', [
                'key' => 'd77bfa2bca5a461ebfa5d9cfec834a28',
                'info' => $content,
                'userid' => $id,
            ], true);

            return isset($result['url']) ? $result['text'] . $result['url'] : $result['text'];
        } catch (\Exception $e) {
            return '图灵API连不上了，再问问试试';
        }
    }
}
