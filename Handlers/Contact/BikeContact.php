<?php
/**
 * Description:
 * User: administere
 * Date: 2017/6/29
 * Time: 12:59
 */

namespace Guandaxia\Handlers\Contact;

use Guandaxia\Handlers\Service\OfoBike;
use Guandaxia\Handlers\Service\Password;
use Hanson\Vbot\Contact\Friends;
use Hanson\Vbot\Contact\Groups;
use Hanson\Vbot\Message\Image;
use Hanson\Vbot\Message\Text;
use Illuminate\Support\Collection;

class BikeContact
{
    private static $ofoLogin = [];
    private static $ofoRepair;

    public static function messageHandler(Collection $message, Friends $friends, Groups $groups){
        $path = realpath(vbot("config")['path'])."/";
        $username = $message['from']['UserName'];
        $isLogin = isset(static::$ofoLogin[$username]);

        if ($message['content'] == 'ofo登录') {
            static::$ofoLogin[$username] = [
                'tel' => '',
                'captcha' => '',
                'code' => '',
            ];
            Text::send($username, "请先输入手机号");
        } elseif (preg_match('/^1\d{10}$/', $message['content'], $match) && $isLogin) {
            $tel = $match[0];
            static::$ofoLogin[$username]['tel'] = $tel;
            $ofo = new OfoBike();
            $result = $ofo->getVerifyCode($tel);
            if ($result['code'] == 1) {
                Text::send($username, $result['msg']);
                $filename = realpath(vbot("config")['path']) . "/ofo/captcha.jpg";
                vbot('console')->log('图片验证码：' . $filename);
                Image::send($username, $filename);
            } else {
                Text::send($username, $result['msg']);
            }

        } elseif (preg_match('/\d{4}$/', $message['content'], $match) && $isLogin) {
            $tel = static::$ofoLogin[$username]['tel'];
            $ofo = new OfoBike();
            if (!empty(static::$ofoLogin[$username]['captcha'])) {
                //短信验证码
                vbot('console')->log('code');
                $code = $match[0];
                $result = $ofo->login($tel, $code);
                if ($result['code'] == 0) {
                    unset(static::$ofoLogin[$username]);
                }
                Text::send($username, $result['msg']);
            } else {
                //图片验证码
                vbot('console')->log('captcha');
                $captcha = $match[0];
                static::$ofoLogin[$username]['captcha'] = $captcha;
                $result = $ofo->getVerifyCode($tel, $captcha);
                if ($result['code'] == 0) {
                    Text::send($username, $result['msg']);
                    Text::send($username, "请输入短信验证码");
                } else {
                    Text::send($username, $result['msg']);
                }
            }
        }
        elseif (preg_match('/^ofo\d{5,8}\s*\d{4}$|^\d{5,8}$/', $message['content'], $match)) {
            //ofo密码查询
            vbot('console')->log('ofo');
            $message = $match[0];
            $passwordService = new Password();
            if (strpos($message, ' ') === false) {
                //查询
                $text = $passwordService->queryOfo($message);
                if ($text == "未找到该密码") {
                    $text = "正在向ofo查询，请稍等";
                    Text::send($username, $text);

                    $ofo = new OfoBike();
                    vbot('console')->log($message);
                    $result = $ofo->query($message, $username);
                    $text = $result['msg'];
                    if($result['code'] == 40014){
                        //多次报修
                        self::$ofoRepair = 1;
                    }
                }
            } else {
                //添加
                list($account, $password) = explode(" ", $message);
                $text = $passwordService->addOfo($account, $password);
            }
            Text::send($username, $text);
        }
        elseif(($message['content'] === '1' || $message['content'] === '是') && self::$ofoRepair == 1){
            //该车辆多次报修，继续使用
            self::$ofoRepair = 0;
            $ofo = new OfoBike();
            $result = $ofo->query('', $username);
            $text = $result['msg'];
            Text::send($username, $text);
        }
        elseif ($message['content'] === '结束行程') {
            //ofo结束行程
            $ofo = new OfoBike();
            $result = $ofo->end();
            Text::send($username, $result['msg']);

        }
        elseif ($message['content'] === '支付') {
            //ofo支付
            $ofo = new OfoBike();
            $result = $ofo->pay();
            Text::send($username, $result['msg']);
        }
        return ;
    }
}