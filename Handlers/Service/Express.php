<?php
/**
 * Description:
 * User: administere
 * Date: 2017/11/24
 * Time: 11:24
 */

namespace Guandaxia\Handlers\Service;


use Curl\Curl;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\db\exception\PDOException;
use think\Exception;

class Express
{
    public function __construct()
    {
    }

    /**
     * 获取需要查询的用户名和快递单号
     * @throws \Exception
     * @return array
     */
    public function getExpressList()
    {
//        $date = new \DateTime();
//        $date->modify('-7 day');
//        $date->format('Y-m-d');
        $date = date('Y-m-d H:i:s', strtotime('-7 day'));

        try {
            $expressList = Db::table('express')
                ->whereTime('updated_at', '<', $date)
                ->select();
        } catch (DataNotFoundException $e) {
        } catch (ModelNotFoundException $e) {
        } catch (DbException $e) {
        }

        if(empty($expressList)){
            return [];
        }
        $expressUserList = [];
        foreach ($expressList as $item) {
            $userName = $item['user_name'];
            $expressNumber = $item['express_number'];
            $expressCode = $item['express_code'];

            vbot('console')->log($expressNumber);
            if(empty($expressCode)){
                try{
                    $expressCode = $this->getCompany($expressNumber);
                }catch (\Exception $e){
                    continue;
                }
            }

            $oldExpressInfo = json_decode($item['expressInfo'], true);
            $newExpressInfo = $this->getExpressInfo($expressNumber, $expressCode);
            if(empty($newExpressInfo)){
                //未查到快递信息
                continue;
            }

//            vbot('console')->log($newExpressInfo);
//            vbot('console')->log($oldExpressInfo);
            if(count($newExpressInfo) > count($oldExpressInfo)){
                //快递信息更新了，保存快递信息
                try {
                    Db::table('express')
                        ->where('id', $item['id'])
                        ->update([
                            'express_info' => $newExpressInfo,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                } catch (PDOException $e) {
                } catch (Exception $e) {
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

//        $query = new Query("Express");
//        $query->greaterThanOrEqualTo("updatedAt", $date);
//        try{
//            $expressList = $query->find();
//        }catch (RuntimeException $exception){
//            vbot('console')->log('express query error:'. $exception->getMessage());
//            return [];
//        }
//
//        if(empty($expressList)){
//            return [];
//        }
//        $expressUserList = [];
//        foreach ($expressList as $item) {
//            $objectId = $item->get('objectId');
//            $userName = $item->get('userName');
//            $expressNumber = $item->get('expressNumber');
//            $expressCode = $item->get('expressCode');
//
//            vbot('console')->log($objectId);
//            vbot('console')->log($expressNumber);
//            if(empty($expressCode)){
//                $expressCode = $this->getCompany($expressNumber);
//            }
//
//            $oldExpressInfo = $item->get('expressInfo');
//            $newExpressInfo = $this->getExpressInfo($expressNumber, $expressCode);
//            if(empty($newExpressInfo)){
//                //未查到快递信息
//                continue;
//            }
//
////            vbot('console')->log($newExpressInfo);
////            vbot('console')->log($oldExpressInfo);
//            if(count($newExpressInfo) > count($oldExpressInfo)){
//                //快递信息更新了，保存快递信息
//
//                $object = new Object('Express', $objectId);
//                $object->set('expressCode', $expressCode);
//                $object->set("expressInfo", $newExpressInfo);
//                try {
//                    $object->save();
//                    vbot('console')->log('保存快递信息成功');
//                } catch (\Exception $ex) {
//                    vbot('console')->log('保存快递信息失败：'.$ex->getMessage());
//                }
//
//                $expressInfo = "📦您的快递信息更新了\r\n";
//                foreach ($newExpressInfo as $key=>$info) {
//                    if($key == 0){
//                        $expressInfo .= sprintf("👉%s %s\r\n\r\n", $info['time'], $info['context']);
//                    }else{
//                        $expressInfo .= sprintf("⬆️%s %s\r\n\r\n", $info['time'], $info['context']);
//                    }
//                }
//
//                $expressUserList[] = [
//                    'userName'  =>   $userName,
//                    'message'   =>  $expressInfo,
//                ];
//            }
//        }
//
//        return $expressUserList;
    }

    public function saveNumber($nickName, $number, $code, $expressData)
    {
        try {
            $result = Db::table('express')
                ->where('user_name', $nickName)
                ->where('express_number', $number)
                ->find();
        } catch (DataNotFoundException $e) {
        } catch (ModelNotFoundException $e) {
        } catch (DbException $e) {
        }
        if($result == null){

            $date = date('Y-m-d H:i:s');
            $data = [
                'user_name' =>  $nickName,
                'express_number' =>$number,
                'express_code'  =>  $code,
                'express_info'  =>  json_encode($expressData),
                'created_at'    => $date,
                'updated_at'    =>  $date,
            ];
            Db::table('express')
                ->data($data)
                ->insert();
//            print_r(Db::getSqlLog());
            vbot('console')->log('保存快递单号成功');
        }

//        $userNameQuery = new Query('Express');
//        $userNameQuery->equalTo('userName', $nickName);
//
//        $expressNumberQuery = new Query('Express');
//        $expressNumberQuery->equalTo('expressNumber', $number);

//        $query = Query::doCloudQuery("select ")
//        $query = Query::andQuery($userNameQuery, $expressNumberQuery);
//
//        try {
//            $query->first();
//        } catch (CloudException $e) {
//            $object = new Object('Express');
//            $object->set("expressNumber", $number);
//            $object->set("userName", $nickName);
//            try {
//                $object->save();
//                vbot('console')->log('保存快递单号成功');
//            } catch (\Exception $ex) {
//                vbot('console')->log('保存快递单号失败：'.$ex->getMessage());
//            }
//        }
    }

    public function query($nickName, $number)
    {
        try{
            $code = $this->getCompany($number);
            if($code == ''){
                vbot('console')->log('快递公司识别错误');
                return '😂😂😂😂😂😂😂快递公司识别错误';
            }
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
                    $this->saveNumber($nickName, $number, $code, $result['data']);
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
            $errMsg = 'File: '.$exception->getFile().
                '\nLine: '. $exception->getLine().
                '\nMessage: '. $exception->getMessage() .
                '\nTrace: '. $exception->getTraceAsString();
            vbot('console')->log('快递查询错误：'.$errMsg);
            return '😂😂😂😂😂😂😂系统错误，请稍候重试';
        }
    }

    /**
     * @param $number
     * @param string $code
     * @return array
     * @throws \ErrorException
     */
    private function getExpressInfo($number, $code = '')
    {
        if(empty($code)){
            try{
                $code = $this->getCompany($number);
            }catch (\Exception $e){
                vbot('console')->log($e);
                return [];
            }
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
            $result = $curl->response;
            if($result->status == 200){
                return $result->data;
            }
        }
        return [];
    }

    /**
     * @param $number
     * @return string
     * @throws \ErrorException
     * @throws \Exception
     */
    private function getCompany($number)
    {
        $url = "https://www.kuaidi100.com/autonumber/autoComNum";
        $curl = new Curl();
        $curl->post($url, ['resultv2'=>1, 'text'=>$number]);
        if ($curl->error) {
            $msg =  'Express Query Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
            vbot('console')->log($msg);
            throw new \Exception($curl->errorMessage);
        } else {
//            $result = json_decode($curl->response, true);
            $auto = $curl->response->auto;
            if(count($auto)){
                $code = $auto[0]->comCode;
                return $code;
            }
            return '';
        }
    }

}