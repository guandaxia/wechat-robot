<?php
/**
 * Description:
 * User: administere
 * Date: 2017/7/17
 * Time: 11:31
 */

namespace Guandaxia\Handlers\Service;

use LeanCloud\Client;
use LeanCloud\CloudException;
use LeanCloud\Object;
use LeanCloud\Query;

class Charge
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

    public function add($info)
    {
        $name = $info['name'];
        $money = $info['money'];
        $type = $info['type'];
        $location = $info['location'];
        list($long, $lat) = explode($location, ',');
        $location = new GeoPoint($long, $lat);

        print_r($info);
        $object = new Object('Charge');
        $object->set('name', $name);
        $object->set('money', $money);
        $object->set('type', $type);
        $object->set('location', $location);

        try {
            $object->save();
            $text = "记账成功";
        } catch (CloudException $ex) {
            echo $ex;
            $text = "记账失败，请重试:". $ex;
        }
        return $text;
    }



}