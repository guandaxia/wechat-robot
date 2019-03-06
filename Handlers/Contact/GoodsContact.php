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
        $count = preg_match_all('/￥/', $message['content'], $match);
        if ($count >= 2) {
            Text::send($message['from']['UserName'], '正在查找优惠券');
            //淘口令
            $goodsService = new GoodsService();
            try {
                $result = $goodsService->getGoodsInfo($message['content']);
                print_r($result);
                vbot('console')->log('优惠券信息:');
                vbot('console')->log(json_encode($result, JSON_UNESCAPED_UNICODE));
                Text::send($message['from']['UserName'], '找到优惠券了~');
                $resultMessage = <<<EOF
商品金额：{$result->origin_price}
优惠券金额：{$result->quan_price}
券后金额：{$result->current_price}
口令： {$result->token}
EOF;
                Text::send($message['from']['UserName'], $resultMessage);
            } catch (\Exception $e) {
                Text::send($message['from']['UserName'], '系统错误'.$e->getMessage());
            }

        }
    }
}