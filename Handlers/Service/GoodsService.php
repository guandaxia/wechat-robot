<?php
/**
 * Description:
 * User: guansixu
 * Date: 2019-01-18
 * Time: 10:17
 */

namespace Guandaxia\Handlers\Service;

use Curl\Curl;

class GoodsService
{
    private $appKey;
    private $pid;

    public function __construct($pid = 'mm_119302567_361000046_100110900291')
    {

        $this->appKey = '5rto1sxq';
        $this->pid    = $pid ?? 'mm_119302567_361000046_100110900291';
        $this->curl   = new Curl();
    }

    /**
     * @param string $content
     * @throws \Exception
     * @return array
     */
    public function getGoodsInfo($content)
    {
        $goodsId = $this->getGoodsId($content);
        $couponInfo = $this->getCouponCode($goodsId);
        return $couponInfo;
    }

    /**
     * 获取goodsId
     * @param $content
     * @throws \Exception
     * @return string
     */
    private function getGoodsId($content)
    {
        $couponDetailUrl = 'http://tkapi.apptimes.cn/token/get-token';

        $result = $this->curl->get($couponDetailUrl, [
            'appkey' => $this->appKey,
            'token' => $content,
        ]);

        if ($result->errcode != 0) {
            // 获取优惠券错误
            throw new \Exception('获取商品信息错误：'.$result->errmsg);
        }
        return $result->data->item_id;
    }

    /**
     * @param string $goodsId
     * @return array
     * @throws \Exception
     */
    private function getCouponDetail($goodsId)
    {
//            $pid = 'mm_124643220_55850312_10088050376';
//        $goodsId = '525613972060';
//            $goodsId = '10031645140';
        $couponDetailUrl = 'http://tkapi.apptimes.cn/coupon';

        $couponInfo = [];
        // 获取优惠券详情
        $result = $this->curl->get($couponDetailUrl, [
            'appkey' => $this->appKey,
            'pid' => $this->pid,
            'good_id' => $goodsId,
        ]);

        if ($result->errcode != 0) {
            // 获取优惠券错误
            throw new \Exception('获取优惠券错误：'.$result->errmsg);
        }
        $data = $result->data;
        if (empty($data->coupon_remain_count) || empty($data->coupon_click_url)) {
            // 优惠券为空
            throw new \Exception('优惠券已失效');
        }
        $couponInfo['coupon_url']          = $data->coupon_click_url;
        $couponInfo['coupon_end_time']     = $data->coupon_end_time;
        $couponInfo['coupon_info']         = $data->coupon_info;
        $couponInfo['coupon_remain_count'] = $data->coupon_remain_count;
        $couponInfo['coupon_start_time']   = $data->coupon_start_time;
        $couponInfo['coupon_total_count']  = $data->coupon_total_count;

        $result = preg_match_all('/\d+\.\d+|\d+/', $couponInfo['coupon_info'], $matches, PREG_SET_ORDER, 0);
        if ($result) {
            $couponInfo['coupon_price'] = $matches[1][0];
        }
        return $couponInfo;
    }

    /**
     * 获取优惠券口令
     * @param string $goodsId
     * @return array
     * @throws \Exception
     */
    public function getCouponCode($goodsId)
    {
        $codeUrl = 'http://tkapi.apptimes.cn/convert/id-to-token';

        $result = $this->curl->get($codeUrl, [
            'appkey' => $this->appKey,
            'pid' => $this->pid,
            'good_id' => $goodsId,
        ]);
        if ($result->errcode != 0) {
            // 获取优惠券错误
            throw new \Exception('获取优惠券错误:'. $result->errmsg);
        }
//        dump($result);
//        $couponInfo['coupon_code'] = $result->data->token;
        return $result->data;
    }

    /**
     * 获取优惠券口令
     * @param string $goodsId
     * @return string
     * @throws \Exception
     */
    private function getGoodsDetail($goodsId)
    {
        $goodsDetailUrl = 'https://acs.m.taobao.com/h5/mtop.taobao.detail.getdetail/6.0';

        $result = $this->curl->get($goodsDetailUrl, [
            'data' => '{"itemNumId":"' . $goodsId . '"}',
        ]);
        if ($result->ret[0] != 'SUCCESS::调用成功') {
            // 获取商品详情错误
            throw new \Exception("获取商品详情错误");
        }
//        dump($result->data);
        $value = $result->data->apiStack[0]->value;
        $value = json_decode($value, true);
//        dump($value);
        $priceResult = preg_match_all('/\d+\.\d+|\d+/', $value['price']['price']['priceText'], $matches, PREG_SET_ORDER, 0);
        if ($priceResult) {
            $goodsInfo['goods_price'] = $matches[0][0];
        }

        $priceResult = preg_match_all('/\d+\.\d+|\d+/', $value['price']['extraPrices'][0]['priceText'], $matches, PREG_SET_ORDER, 0);
        if ($priceResult) {
            $goodsInfo['old_goods_price'] = $matches[0][0];
        }

//        $goodsInfo['goods_price']     = $value['price']['price']['priceText'];
//        $goodsInfo['old_goods_price'] = $value['price']['extraPrices'][0]['priceText'];
        $goodsInfo['goods_name'] = $result->data->item->title;
        $goodsInfo['sell_count'] = $value['item']['sellCount'];
        $goodsInfo['images']     = $result->data->item->images;
        return $goodsInfo;
    }
}