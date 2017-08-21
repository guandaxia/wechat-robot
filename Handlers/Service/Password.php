<?php
/**
 * Description:
 * User: administere
 * Date: 2017/6/22
 * Time: 16:03
 */

namespace Guandaxia\Handlers\Service;

use LeanCloud\Client;
use LeanCloud\CloudException;
use LeanCloud\Object;
use LeanCloud\Query;

class Password
{
    public function __construct()
    {
        $config = vbot('config');
        $appid = $config['leancloud.appid'];
        $appkey = $config['leancloud.appkey'];
        $masterkey = $config['leancloud.masterkey'];
        Client::initialize($appid, $appkey, $masterkey);
//        Client::setDebug(true);
    }

    public function queryOfo($account)
    {
        $query = new Query('Account');
        $query->equalTo('account', $account);
        try {
            $info = $query->first();
            $password = $info->get("password");
            $text = sprintf("查询到的密码：%s", $password);
        } catch (CloudException $e) {
            $text = "未找到该密码";
        }

        return $text;
    }

    public function addOfo($account, $password)
    {
        $ofoObject = new Object("Account");
        $ofoObject->set("account", $account);
        $ofoObject->set("password", $password);
        try {
            $ofoObject->save();
            $text = "保存成功";
        } catch (CloudException $ex) {
            $text = "保存失败，请重试";
        }
        return $text;
    }

}