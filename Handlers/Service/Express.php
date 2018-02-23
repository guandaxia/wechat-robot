<?php
/**
 * Description:
 * User: administere
 * Date: 2017/11/24
 * Time: 11:24
 */

namespace Guandaxia\Handlers\Service;


use Curl\Curl;
use LeanCloud\Client;
use LeanCloud\CloudException;
use LeanCloud\Object;
use LeanCloud\Query;
use Symfony\Component\Translation\Exception\RuntimeException;

class Express
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

    /**
     * 获取需要查询的用户名和快递单号
     * @return array
     */
    public function getExpressList()
    {
        $date = new \DateTime();
        $date->modify('-7 day');
        $date->format('Y-m-d');
        $query = new Query("Express");
        $query->greaterThanOrEqualTo("updatedAt", $date);
        try{
            $expressList = $query->find();
        }catch (RuntimeException $exception){
            vbot('console')->log('express query error:'. $exception->getMessage());
            return [];
        }

        if(empty($expressList)){
            return [];
        }
        $expressUserList = [];
        foreach ($expressList as $item) {
            $objectId = $item->get('objectId');
            $userName = $item->get('userName');
            $expressNumber = $item->get('expressNumber');
            $expressCode = $item->get('expressCode');

            vbot('console')->log($objectId);
            vbot('console')->log($expressNumber);
            if(empty($expressCode)){
                $expressCode = $this->getCompany($expressNumber);
            }

            $oldExpressInfo = $item->get('expressInfo');
            $newExpressInfo = $this->getExpressInfo($expressNumber, $expressCode);
            if(empty($newExpressInfo)){
                //未查到快递信息
                continue;
            }

//            vbot('console')->log($newExpressInfo);
//            vbot('console')->log($oldExpressInfo);
            if(count($newExpressInfo) > count($oldExpressInfo)){
                //快递信息更新了，保存快递信息

                $object = new Object('Express', $objectId);
                $object->set('expressCode', $expressCode);
                $object->set("expressInfo", $newExpressInfo);
                try {
                    $object->save();
                    vbot('console')->log('保存快递信息成功');
                } catch (\Exception $ex) {
                    vbot('console')->log('保存快递信息失败：'.$ex->getMessage());
                }

                $expressInfo = "📦您的快递信息更新了\r\n";
                foreach ($newExpressInfo as $key=>$info) {
                    if($key == 0){
                        $expressInfo .= sprintf("👉%s %s\r\n\r\n", $info['time'], $info['context']);
                    }else{
                        $expressInfo .= sprintf("⬆️%s %s\r\n\r\n", $info['time'], $info['context']);
                    }
                }

                $expressUserList[] = [
                    'userName'  =>   $userName,
                    'message'   =>  $expressInfo,
                ];
            }
        }

        return $expressUserList;
    }

    public function saveNumber($nickName, $number)
    {
        $userNameQuery = new Query('Express');
        $userNameQuery->equalTo('userName', $nickName);

        $expressNumberQuery = new Query('Express');
        $expressNumberQuery->equalTo('expressNumber', $number);

//        $query = Query::doCloudQuery("select ")
        $query = Query::andQuery($userNameQuery, $expressNumberQuery);

        try {
            $query->first();
        } catch (CloudException $e) {
            $object = new Object('Express');
            $object->set("expressNumber", $number);
            $object->set("userName", $nickName);
            try {
                $object->save();
                vbot('console')->log('保存快递单号成功');
            } catch (\Exception $ex) {
                vbot('console')->log('保存快递单号失败：'.$ex->getMessage());
            }
        }
    }

    public function query($number)
    {
        try{
            $code = $this->getCompany($number);
            $url = "https://www.kuaidi100.com/query";
            $curl = new Curl();
            $curl->get($url, ['type'=>$code, 'postid'=>$number]);
            if ($curl->error) {
                $msg =  'Express Query Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
                vbot('console')->log($msg);
                return '😂系统错误，请稍候重试';
            } else {
                vbot('console')->log($curl->response);
                $result = json_decode($curl->response, true);
                if($result['status'] == 201){
                    //快递公司参数异常：单号不存在或者已经过期
                    return $result['message'];
                }elseif($result['status'] == 400) {
                    //参数错误
                    return '😂参数错误';
                }elseif ($result['status'] == 200){
                    $data = $result['data'];
                    $result = '';
                    foreach ($data as $key=>$datum) {
                        if($key == 0){
                            $result .= sprintf("👉%s %s\r\n\r\n", $datum['time'], $datum['context']);
                        }else{
                            $result .= sprintf("⬆️%s %s\r\n\r\n", $datum['time'], $datum['context']);
                        }
                    }
                    vbot('console')->log($result);
                    return $result;
                }
            }
        }catch (\Exception $exception){
            vbot('console')->log('快递查询错误：'.$exception->getMessage());
            return '😂😂😂😂😂😂😂系统错误，请稍候重试';
        }
    }

    private function getExpressInfo($number, $code = '')
    {
        if(empty($code)){
            $code = $this->getCompany($number);
        }
        $url = "https://www.kuaidi100.com/query";
        $curl = new Curl();
        $curl->get($url, ['type'=>$code, 'postid'=>$number]);
        if ($curl->error) {
            $msg =  'Express Query Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
            vbot('console')->log($msg);
            return [];
        } else {
            vbot('console')->log($curl->response);
            $result = json_decode($curl->response, true);
            if($result['status'] == 200){
                return $result['data'];
            }
        }
        return [];
    }

    private function getCompany($number){
        $url = "https://www.kuaidi100.com/autonumber/autoComNum";
        $curl = new Curl();
        $curl->post($url, ['resultv2'=>1, 'text'=>$number]);
        if ($curl->error) {
            $msg =  'Express Query Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
            vbot('console')->log($msg);
            throw new \Exception($curl->errorMessage);
        } else {
            $result = json_decode($curl->response, true);
            $code = $result['auto'][0]['comCode'];
            return $code;
        }
    }

}