<?php

namespace Guandaxia;

use Hanson\Vbot\Support\File;

class Observer
{
    public static function setQrCodeObserver($qrCodeUrl)
    {
        vbot('console')->log('二维码链接：'.$qrCodeUrl, '系统消息');
    }

    public static function setLoginSuccessObserver()
    {
        vbot('console')->log('登录成功', '系统消息');
    }

    public static function setReLoginSuccessObserver()
    {
        vbot('console')->log('免扫码登录成功', '系统消息');
    }

    public static function setExitObserver()
    {
        $friends = vbot('friends');
        $userName = $friends->getUsernameByNickname('管思旭');
        Text::send($userName, '小号退出');
        vbot('console')->log('退出程序', '系统消息');
    }

    public static function setFetchContactObserver(array $contacts)
    {
        vbot('console')->log('获取好友成功', '系统消息');
        File::saveTo(__DIR__.'/group.json', $contacts['groups']);
    }

    public static function setBeforeMessageObserver()
    {
        vbot('console')->log('准备接收消息', '系统消息');
    }

    public static function setNeedActivateObserver()
    {
        $friends = vbot('friends');
        $userName = $friends->getUsernameByNickname('管思旭');
        Text::send($userName, '小号快挂了');
        vbot('console')->log('准备挂了，但应该能抢救一会', '系统消息');
    }

}
