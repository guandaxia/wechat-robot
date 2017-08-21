<?php
/**
 * Description:
 * User: guansixu
 * Date: 2017/6/27
 * Time: 13:16
 */

namespace Guandaxia\Handlers\Service;


class OfoBike
{
    private $tokenName;
    private $path;
    private $carnoCache = 'ofo-carno';
    private $orderInfoCache = 'ofo-order-info';

    public function __construct()
    {
        $this->path = realpath(vbot("config")['path']) . "/ofo/";
        if(!is_dir($this->path)){
            mkdir($this->path, true, 700);
        }
        $this->tokenName = $this->path. "token.txt";
    }

    //获取验证码
    public function getVerifyCode($tel, $captcha='')
    {
        $url = "https://san.ofo.so/ofo/Api/v2/getVerifyCode";
        $params = [
            'source' => 0,
            'tel' => $tel,
            'captcha' => $captcha,
        ];
        $result = $this->http($url, $params);
        if ($result['errorCode'] == '13010') {
            $message = $result['msg'];
            $data = [
                'code'  =>  1,
                'msg'   =>  $result['msg'],
            ];
            //保存验证码图片
            $val = $result['values']['captcha'];
            $this->base64ToImg($val);
        } elseif($result['errorCode'] == '200') {
            $data = [
                'code'  =>  0,
                'msg'   =>  $result['msg'],
            ];
        }
        return $data;
    }

    //登录
    public function login($tel, $code)
    {
        $url = "https://san.ofo.so/ofo/Api/login";
        $params = [
            'source' => 0,
            'tel' => $tel,
            'code' => $code,
        ];
        $result = $this->http($url, $params);
        if($result['errorCode'] == 200){
            file_put_contents($this->tokenName, $result['values']['token']);
//            vbot('cache')->forever($this->tokenName, $result['values']['token']);
            $data = [
                'code'  =>  0,
                'msg'   =>  $result['msg'],
            ];
        }else{
            $data = [
                'code'  =>  $result['errorCode'],
                'msg'   =>  $result['msg'],
            ];
        }
        return $data;
    }

    //查询密码
    public function query($carno='', $userName='')
    {
        $continue = 0;
        $carnoCaheName = $this->carnoCache. $userName;
        if(empty($carno) && vbot('cache')->has($carnoCaheName)){
            $carno = vbot("cache")->get($carnoCaheName);
            $continue = 1;
        }
        elseif(empty($carno)){
            $data = [
                'code'  =>  1,
                'msg'   =>  '请输入车号',
            ];
            return $data;
        }

//        $token = vbot("cache")->get($this->tokenName);
        $token = file_get_contents($this->tokenName);
        vbot('console')->log('ofo-token:'.$token);
        if(empty($token)){
            $data = [
                'code'  =>  1,
                'msg'   =>  '请先登录',
            ];
            return $data;
        }

        //随机伪造经纬度
        $lat = '35.68'.mt_rand(0, 999);
        $lng = '139.68'. mt_rand(0, 999);

        $url = "https://san.ofo.so/ofo/Api/v2/carno";
        $params = [
            'source' => 0,
            'token' =>  $token,
            'carno' => $carno,
            'lat'   =>  $lat,
            'lng'   =>  $lng,
            'continue' => $continue,
        ];
        $result = $this->http($url, $params);
        if($result['errorCode'] == 200){
            $carno = $result['values']['info']['carno'];
            $password = $result['values']['info']['pwd'];
            $orderNo = $result['values']['info']['orderno'];

            //保存帐号和密码
            $passwordHanle = new Password();
            $passwordHanle->addOfo($carno, $password);

            //把订单号写入缓存，用于结束订单
            $orderInfo = [
                'time'  =>  time(),
                'order_no'  =>  $orderNo,
                'user_name' =>  $userName,
            ];
            vbot('cache')->forever($this->orderInfoCache, json_encode($orderInfo));

            $msg = sprintf("车号：%s\r\n密码：%s", $carno, $password);
            $data = [
                'code'  =>  0,
                'msg'   =>  $msg,
            ];
        }
        elseif($result['errorCode'] == 40014){
            //报修
            vbot('cache')->put($carnoCaheName, $carno, 2);
            $msg = sprintf("%s\n继续使用请回复1或是", $result['msg']);
            $data = [
                'code'  =>  $result['errorCode'],
                'msg'   =>  $msg,
            ];
        }
        else{
            $data = [
                'code'  =>  $result['errorCode'],
                'msg'   =>  $result['msg'],
            ];
        }
        return $data;
    }

    //结束订单
    public function end($orderno='')
    {
//        $token = vbot("cache")->get($this->tokenName);
        $token = file_get_contents($this->tokenName);
        if(empty($token)){
            $data = [
                'code'  =>  1,
                'msg'   =>  '请先登录',
            ];
            return $data;
        }

        if(empty($orderno)){
            $orderInfo = vbot('cache')->get($this->orderInfoCache);
            $orderInfo = json_decode($orderInfo, true);
            $orderno = $orderInfo['order_no'];
        }

        $url = "https://san.ofo.so/ofo/Api/v2/end";
        //随机伪造经纬度
        $lat = '35.68'.mt_rand(0, 999);
        $lng = '139.68'. mt_rand(0, 999);
        $params = [
            'source' => 0,
            'orderno' => $orderno,
            'token' => $token,
            'lat'   =>  $lat,
            'lng'   =>  $lng,
        ];
        $result = $this->http($url, $params);
        if($result['errorCode'] == 200){
            $data = [
                'code'  =>  0,
                'msg'   =>  $result['msg'],
            ];
        }else{
            $data = [
                'code'  =>  $result['errorCode'],
                'msg'   =>  $result['msg'],
            ];
        }
        return $data;
    }

    public function pay($orderno = '')
    {
//        $token = vbot("cache")->get($this->tokenName);
        $token = file_get_contents($this->tokenName);
        if(empty($token)){
            $data = [
                'code'  =>  1,
                'msg'   =>  '请先登录',
            ];
            return $data;
        }

        if(empty($orderno)){
            $orderInfo = vbot('cache')->pull($this->orderInfoCache);
            $orderInfo = json_decode($orderInfo, true);
            $orderno = $orderInfo['order_no'];
        }

        $url = "https://san.ofo.so/ofo/Api/v2/pay";
        //随机伪造经纬度
        $lat = '35.68'.mt_rand(0, 999);
        $lng = '139.68'. mt_rand(0, 999);
        $params = [
            'source' => 0,
            'orderno' => $orderno,
            'token' => $token,
            'packetid' => 0,
            'lat'   =>  $lat,
            'lng'   =>  $lng,
        ];
        $result = $this->http($url, $params);
        vbot('console')->log(json_encode($result));
        if($result['errorCode'] == 200){
            $msg = sprintf("%s\r\n点击链接领取红包 %s", $result['msg'], $result['values']['info']['url']);
            $data = [
                'code'  =>  0,
                'msg'   =>  $msg,
            ];
        }else{
            $data = [
                'code'  =>  $result['errorCode'],
                'msg'   =>  $result['msg'],
            ];
        }
        return $data;
    }

    //获取报修类型
    public function repair($orderno = '')
    {
        $token = file_get_contents($this->tokenName);
        if(empty($token)){
            $data = [
                'code'  =>  1,
                'msg'   =>  '请先登录',
            ];
            return $data;
        }

        if(empty($orderno)){
            $orderInfo = vbot('cache')->pull($this->orderInfoCache);
            $orderInfo = json_decode($orderInfo, true);
            $orderno = $orderInfo['order_no'];
        }

        $url = "https://san.ofo.so/ofo/Api/dict";
        $params = [
            'source' => 0,
            'orderno' => $orderno,
            'token' => $token,
        ];
        $result = $this->http($url, $params);
        vbot('console')->log(json_encode($result));
        if($result['errorCode'] == 200){
            $msg = $result['values']['info'];
            $data = [
                'code'  =>  0,
                'msg'   =>  $msg,
            ];
        }else{
            $data = [
                'code'  =>  $result['errorCode'],
                'msg'   =>  $result['msg'],
            ];
        }
        return $data;

    }

    public function unfinished()
    {
        $token = file_get_contents($this->tokenName);
        if(empty($token)){
            return false;
        }
        $url = "https://san.ofo.so/ofo/Api/v2/unfinished";

        $params = [
            'source' => 0,
            'token' => $token,
        ];
        $result = $this->http($url, $params);
        if($result['errorCode'] == 30005){
            $refreshTime = $result['values']['info']['refreshTime'];
            $ordernum = $result['values']['info']['ordernum'];
            return ['refreshTime'=>$refreshTime, 'ordernum'=>$ordernum];
        }else{
            return false;
        }
    }

    private function base64ToImg($string)
    {
        if (empty($string)) {
            return false;
        }
        $string = base64_decode($string);
//        $path = realpath(vbot("config")['path']) . "/ofo/";
//        if (!is_dir($path)) {
//            mkdir($path, 0700, true);
//        }
        $filename = $this->path . "captcha.jpg";
        file_put_contents($filename, $string);
        return true;
    }

    private function http($url, $params)
    {
        if (empty($url)) {
            return [];
        }

        $result = vbot('http')->post($url, $params);
        $result = json_decode($result, true);
        return $result;
    }
}