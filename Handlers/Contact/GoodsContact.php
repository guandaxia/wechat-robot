<?php
/**
 * Description:
 * User: guansixu
 * Date: 2019-03-06
 * Time: 16:34
 */

namespace Guandaxia\Handlers\Contact;

use Guandaxia\Handlers\Service\GoodsService;
use Hanson\Vbot\Contact\Friends;
use Hanson\Vbot\Contact\Groups;
use Hanson\Vbot\Message\Text;
use Illuminate\Support\Collection;

class GoodsContact
{
    public static function messageHandler(Collection $message, Friends $friends, Groups $groups)
    {
        $count = preg_match_all('/￥.+￥/', $message['content'], $match);
        if ($count >= 1) {
            Text::send($message['from']['UserName'], '正在查找优惠券');
            //淘口令
            $goodsService = new GoodsService();
            try {
                $result = $goodsService->getCouponInfo($match[0]);
                vbot('console')->log('优惠券信息:');
                vbot('console')->log(json_encode($result, JSON_UNESCAPED_UNICODE));
                Text::send($message['from']['UserName'], '找到优惠券了~');
                $resultMessage = <<<EOF
优惠券信息：{$result->coupon_info}
优惠券金额：{$result->youhuiquan}
口令： {$result->tpwd}
EOF;
                Text::send($message['from']['UserName'], $resultMessage);
            } catch (\Exception $e) {
                Text::send($message['from']['UserName'], '系统错误'.$e->getMessage());
            }

        }
    }
}