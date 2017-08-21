<?php
/**
 * Description:
 * User: administere
 * Date: 2017/7/17
 * Time: 10:52
 */

namespace Guandaxia\Handlers\Contact;

use Guandaxia\Handlers\Service\Charge;
use function GuzzleHttp\Psr7\parse_query;
use Hanson\Vbot\Contact\Friends;
use Hanson\Vbot\Contact\Groups;
use Hanson\Vbot\Message\Text;
use Illuminate\Support\Collection;
use LeanCloud\GeoPoint;

class ChargeContact
{
    private static $chargeInfo = [];
    private static $type = ['一般', '娱乐', '用餐', '交通', '房租', '衣服', '通讯', '旅游', '化妆', '零食'];

    public function __construct()
    {

    }

    public static function messageHandler(Collection $message, Friends $friends, Groups $groups){
        $username = $message['from']['UserName'];
        if ($message['content'] == '记账'){
            self::$chargeInfo[$username] = [
                'name'  =>  $message['from']['NickName'],
                'step'  =>  1,
                'create_time'   => time(),
            ];
            Text::send($username, "记账开始\n请输入记账金额：");
            return;
        }

        if(empty(self::$chargeInfo[$username])){
            return;
        }

        $step = self::$chargeInfo[$username]['step'];
        $createTime = self::$chargeInfo[$username]['create_time'];
        if($step > 4 || $step < 1 || $createTime > (time() + 600)){
            self::$chargeInfo[$username] = [];
            return;
        }
        if($message['content'] == '修改'){
            self::$chargeInfo[$username] = [
                'step'  =>  1,
                'create_time'   => time(),
            ];
            Text::send($username, '请重新输入记账金额：');
            return;
        }
        if($message['content'] == '取消'){
            self::$chargeInfo[$username] = [];
            Text::send($username, '已取消记账，可以重新输入“”“”“"记账”"来开始记账');
            return;
        }
        if($message['content'] == '0'){
            $step = 3;
        }

        switch ($step){
            case 1:
                //金额
                if(!preg_match('/^\d{1,}[\.]?\d{1,2}$/', $message['content'], $match)){
                    Text::send($username, "请输入正确的金额”");
                    return;
                }
                $money = $match[0];
                $money = floatval($money);
                self::$chargeInfo[$username]['step'] = 2;
                self::$chargeInfo[$username]['money'] = $money;
                $tipsInfo = "请选择记账类型：\n";
                $chargeType = self::$type;
                foreach ($chargeType as $key=>$item) {
                    $tipsInfo .= $key+1 .". ". $item. "\n";
                }
                $tipsInfo .= "可以输入序号或类型\n重新输入金额请输入‘修改’";
                Text::send($username, $tipsInfo);
                break;
            case 2:
                //类型
                if(preg_match('/^\d{1,3}$/', $message['content'], $match)){
                    $type = self::$type[$match[0]+1];
                }else{
                    $type = $message['content'];
                }
                vbot('console')->log('记账类型“”：'.$type);

                self::$chargeInfo[$username]['type'] = $type;
                self::$chargeInfo[$username]['step'] = 3;

                $text = "请输入位置(非必须，结束请输入0)：\n";
                Text::send($username, $text);
                break;
            case 3:
                //地址
                //http://apis.map.qq.com/uri/v1/geocoder?coord=39.091671,117.121651
                if(!empty($message['url'])){
                    $coord = parse_url($message['url']);
                }else{
                    $coord = "0,0";
                }

                self::$chargeInfo[$username]['location'] = $coord;
                self::$chargeInfo[$username]['step'] = 4;

                $chargeObject = new Charge();
                $text = $chargeObject->add(self::$chargeInfo[$username]);
                Text::send($username, $text);
                break;
        }
        return;
    }
}