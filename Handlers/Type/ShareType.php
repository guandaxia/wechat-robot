<?php
/**
 * Description:
 * User: guansixu
 * Date: 2018/2/23
 * Time: 下午8:16
 */

namespace Guandaxia\Handlers\Type;


use Curl\Curl;
use Guandaxia\Handlers\Service\GoodsService;
use Hanson\Vbot\Contact\Friends;
use Hanson\Vbot\Contact\Groups;
use Hanson\Vbot\Message\Text;
use Illuminate\Support\Collection;

class ShareType
{
    //https://common.ofo.so/packet/regular_packet_v2.html?random=https%3A%2F%2Fimg.ofo.so%2Fcms%2F7d0ed865c419f1926a729e0671ca0fe8.jpg%2C#817504129/aca5330424c8228826b82a775a1609f679307ba44a2f95ba43b4e8d6728cd2dc12f0addbce322aef20ecf0a4e4f524ec22d70e01f17f8e7d5be218e368982874b41d57b208e49fa23e64b53d9eb47ce4
    public static function messageHandler(Collection $message, Friends $friends, Groups $groups)
    {
        $username = $message['from']['UserName'];
        if($message['type'] == 'share'){
            vbot('console')->log($message['title']);

            $count = preg_match_all('/￥/', $message['title'], $match);
            if ($count >= 2) {
                Text::send($message['from']['UserName'], '正在查找优惠券');
                //淘口令
                $goodsService = new GoodsService();
                try {
                    $result = $goodsService->getGoodsInfo($message['title']);
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

//            Text::send($message['from']['UserName'], '收到分享:'.$message['title'].$message['description'].$message['app'].$message['url']);
//            $url = $message['url'];
//            $urlInfo = parse_url($url);
//            if($urlInfo['host'] !== 'common.ofo.so'){
////                Text::send($username, '收到分享:'.$message['title'].$message['description'].
////                $message['app'].$message['url']);
//                return;
//            }

//            $fragment = $urlInfo['fragment'];
//            list($ordernum, $key) = explode('/', $fragment);
//
//            $url = 'https://san.ofo.so/ofo/Api/v2/getPacket';
//            $tel = "你的手机号";
//
//            $curl = new Curl();
//            $curl->post($url, compact('ordernum', 'key', 'tel'));
//
//            if ($curl->error) {
//                Text::send($username, '领取红包错误，Error: ' . $curl->errorCode . ': ' . $curl->errorMessage);
//            } else {
//                $response = json_decode(json_encode($curl->response), true);
//                vbot('console')->log(json_encode($response));
//                if($response['errorCode'] == 200){
//                    $amounts = $response['values']['packetList'][0]['amounts'];
//                    Text::send($username, "领取红包成功，金额：{$amounts}元");
//                }else{
//                    //20001   手机号不合法
//                    Text::send($username, $response['msg']);
//                }
//            }
        }
    }
}