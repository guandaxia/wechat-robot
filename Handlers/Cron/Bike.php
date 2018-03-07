<?php

/**
 * Description:
 * User: guansixu
 * Date: 2017/6/28
 * Time: 9:31
 */
namespace Guandaxia\Handlers\Cron;

use Hanson\Vbot\Message\Text;

class Bike
{
    public static function cronHandler()
    {
        $orderInfoCache = "ofo-order-info";

        $ofoBike = new \Guandaxia\Handlers\Service\OfoBike();

        $orderInfo = vbot('cache')->pull($orderInfoCache);
        vbot('console')->log('ofo订单:'. $orderInfo);
        if(empty($orderInfo)){
            //缓存为空
            $result = $ofoBike->unfinished();
            vbot('console')->log('ofo unfinished:'. json_encode($result));
            if ($result == false){
                return ;
            }
            $refreshTime = $result['refreshTime'];
            $ordernum = $result['ordernum'];
            //经过了两百秒
            if($refreshTime < 3400){
//                $ofoBike->end($ordernum);
//                vbot('console')->log('ofo订单结束');
//                $ofoBike->pay($ordernum);
//                vbot('console')->log('ofo订单支付成功');
            }

        }else{
            $orderInfo = json_decode($orderInfo, true);

            if(!empty($orderInfo['time']) && $orderInfo['time'] + 120 < time()){
//                //下单时间超过180秒
//                $ofoBike->end();
//                vbot('console')->log('ofo订单结束');
//                $result = $ofoBike->pay();
//                vbot('console')->log('ofo订单支付成功');
//                if($result['code'] == 0){
//                    Text::send($orderInfo['user_name'], $result['msg']);
//                }
            }
        }
    }
}