<?php
/**
 * Description:
 * User: administere
 * Date: 2017/6/21
 * Time: 13:11
 */

namespace Guandaxia\Handlers\Service;

use Curl\Curl;

class Train
{
    //时刻表
    public function trainTime($trainNumber)
    {
        //http://mobile.12306.cn/weixin/czxx/queryByTrainNo?train_no=010000T18440&from_station_telecode=BBB&to_station_telecode=BBB&depart_date=2017-06-22
        $url = "http://mobile.12306.cn/weixin/czxx/queryByTrainNo";
        //将车次转化为需要的code 010000T18440
        $trainCode = $this->getTrainCode($trainNumber);
        if($trainCode == 0){
            return "未查到，请重试";
        }
        $data = [
            'train_no'              =>  $trainCode,
            'from_station_telecode' =>  "BBB",
            'to_station_telecode'   =>  "BBB",
            'depart_date'           =>  date("Y-m-d", time()),
        ];
//        $url .= "?".http_build_query($data);
        $curl = new Curl();
        $curl->get($url, $data);
        if ($curl->error) {
            $error = 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
            return "未查到，请重试";
        } else {
            $result = $curl->response;
            if(empty($result)){
                return "暂时未查到信息，请稍后重试";
            }
            //print_r($result);
            $result = json_decode(json_encode($result), true);
            $timeInfo = $result['data']['data'];
            $string = '';
            $count = count($timeInfo);
            $startStation = $timeInfo[0]['start_station_name'];         //始发站
            $endStation = $timeInfo[0]['end_station_name'];             //终点站
            $startTime = $timeInfo[0]['start_time'];                    //开车时间

            $string = <<<EOF
    车次：$trainNumber 始发站：$startStation  终到站：$endStation 
——————————————————  
    车站          到时      开时   停留
------------------------------

EOF;
            //始发站
            $info = $timeInfo[0];
            $formatData = [
                ['str'=>1, 'len'=>3],
                ['str'=>$info['station_name'], 'len'=>15],
                ['str'=>"-----", 'len'=>8],
                ['str'=>$info['start_time'], 'len'=>8],
                ['str'=>"-----", 'len'=>6],
            ];
            $string .= $this->formatString($formatData);
//            $string .= $this->formatString(1, $info['station_name'], "----", $info['start_time']);
            for ($i=1; $i < $count-1; $i++){
                $info = $timeInfo[$i];

                $formatData = [
                    ['str'=>$i+1, 'len'=>3],
                    ['str'=>$info['station_name'], 'len'=>15],
                    ['str'=>$info['arrive_time'], 'len'=>8],
                    ['str'=>$info['start_time'], 'len'=>8],
                    ['str'=>$info['stopover_time'], 'len'=>6],
                ];
                $string .= $this->formatString($formatData);
//                $string .= $this->formatString($i+1, $info['station_name'], $info['arrive_time'],
//                    $info['start_time'], $info['stopover_time']);
            }
            //终点站
            $info = $timeInfo[$count-1];
            $formatData = [
                ['str'=>$i+1, 'len'=>3],
                ['str'=>$info['station_name'], 'len'=>15],
                ['str'=>$info['arrive_time'], 'len'=>8],
                ['str'=>"-----", 'len'=>8],
                ['str'=>"-----", 'len'=>6],
            ];
            $string .= $this->formatString($formatData);
//            $string .= $this->formatString($i+1, $info['station_name'], $info['arrive_time']);

            //$string .= sprintf("* 回复车站序号查询%s到达该站正晚点信息", $trainNumber);
            return $string;
        }
    }

    //车次查询
    public function trainNumber($fromStation, $toStation)
    {
        $url = "http://mobile.12306.cn/weixin/leftTicket/query?";

        $formStationCode = $this->getStationCode($fromStation);
        $toStationCode = $this->getStationCode($toStation);

        $param = [
            'leftTicketDTO.train_date'  =>  date("Y-m-d"),
            'leftTicketDTO.from_station'=>  $formStationCode,
            'leftTicketDTO.to_station'  =>  $toStationCode,
            'purpose_codes'             =>  'ADULT',
        ];

        $url .= http_build_query($param);
        $result = vbot('http')->get($url);

        $string = '';
        $result = json_decode($result, true);
//        print_r($result);
        if($result['httpstatus'] == 200){
            $data = $result['data'];
            if(is_array($data)){
                $string .= <<<EOF
  车次  开车时间   到达时间   始发站    终到站   历时
-----------------------------

EOF;
                foreach($data as $key=>$item){
                    $formatData = [
                        ['str'=>$key+1, 'len'=>3],
                        ['str'=>$item['station_train_code'], 'len'=>8],
                        ['str'=>$item['start_time'], 'len'=>8],
                        ['str'=>$item['arrive_time'], 'len'=>8],
                        ['str'=>$item['start_station_name'], 'len'=>15],
                        ['str'=>$item['end_station_name'], 'len'=>15],
                        ['str'=>$item['lishi'], 'len'=>6],
                    ];
                    $string .= $this->formatString($formatData);
                }
            }else{
                $string = "没有查询到车次信息";
            }
        }
        return $string;

    }

    //正晚点查询
    public function trainLate($type=0, $trainNumber='', $station='')
    {
        $url = "http://dynamic.12306.cn/mapping/kfxt/zwdcx/LCZWD/cx.jsp";

        $cacheName = "train_late_". $type. $trainNumber. $station;
        if(empty($trainNumber) && empty($station)){
            //$data = Cache::get('train_late_info');
            $data = vbot("cache")->get($cacheName);
            print_r($data);
        }else{
            $type = $type == "到" ? 0 : 1;
            $trainNumber = strtolower($trainNumber);
            $stationEncode = urlencode($station);
            $stationEncode = str_replace('%', '-', $stationEncode);
            $time = floor(microtime(true)*1000);
            $data = [
                'cz' => $station,
                'cc' => $trainNumber,
                'cxlx' => $type,
                'rq' => date('Y-m-d'),
                'czEn' => $stationEncode,
                'tp' => $time,
            ];
            //Cache::put('train_late_info', $data, 180);
            vbot("cache")->put($cacheName, $data, 180);
        }

        if(empty($data)){
            return "请按照：车次+到/发+站名的格式来查询，如，4481到天津";
        }

//        $url .= "?cz=&". http_build_query($data);
//        echo $url;
        $curl = new Curl();
        //$url = "http://dynamic.12306.cn/mapping/kfxt/zwdcx/LCZWD/cx.jsp?cz=&cc=t123&cxlx=0&rq=2017-06-21&czEn=-E9-83-91-E5-B7-9E&tp=1498030190401";
        $curl->get($url, $data);

        if ($curl->error) {
            $error = 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
            vbot('console')->log($error);
            return "未查到，请重试";
        } else {
            $result = $curl->response;
            if(empty($result)){
                return "暂时未查到信息，请稍后重试";
            }

            $result = mb_convert_encoding($result, 'UTF-8', 'GBK');
            $result = str_replace(["\r", "\n", "\r\n"], "", $result);
            return $result;
        }
    }

//    private function formatString($number, $stationName, $arriveTime, $startTime="----", $stopoverTime="----")
//    {
//        $number = str_pad($number, 3);
//        $stationName = str_pad($stationName, 12);
//        $arriveTime = str_pad($arriveTime, 6);
//        $startTime = str_pad($startTime, 6);
//        $stopoverTime = str_pad($stopoverTime, 5);
//        $string = sprintf("%s %s %s %s %s\r\n\r\n", $number, $stationName, $arriveTime, $startTime, $stopoverTime);
//        return $string;
//    }

    private function formatString($data)
    {
        if (empty($data) || !is_array($data)){
            return '';
        }
        $string = '';
        foreach ($data as $key=>$item) {
            if(!empty($item['str'])){
                $string .=  str_pad($item['str'], $item['len']);
            }else{
                $string .=  str_pad($item['default'], $item['len']);
            }
        }
        $string .= "\r\n";
        return $string;
    }

    private function getTrainCode($trainNumber)
    {
        if(!preg_match('/[\dACDGKLNTYZ]\d{1,4}/ui', $trainNumber, $match)){
            return 0;
        }
        $trainNumber = $match[0];
        $first = substr($trainNumber, 0, 1);
//        vbot('console')->log($first);
        $path = realpath(vbot("config")['path']). "/train/";
//        vbot('console')->log($path);
        $fileName = $path.$first.".json";
        if(is_file($fileName)){
            $result = file_get_contents($fileName);
            $result = json_decode($result, true);
            $trainInfo = $result['data'];
        }else{
            if(!is_dir($path)){
                mkdir($path, 0777, true);
            }
            $url = "http://mobile.12306.cn/weixin/wxcore/queryTrain";
            $data = [
                //?ticket_no=T&depart_date=2017-06-22
                'ticket_no'     =>  $first,
                'depart_date'   =>  date("Y-m-d", time()),
            ];
            $curl = new Curl();
            $curl->get($url, $data);

            if($curl->error){
                $error = 'get train code Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
                vbot('console')->log($error);
                return "未查到，请重试";
            }else{
                $result = $curl->response;
                if(empty($result)){
                    return 0;
                }
                $trainString = json_encode($result);
                file_put_contents($fileName, $trainString);
                $result = json_decode(json_encode($result), true);
                $trainInfo = $result['data'];
            }
        }
        if(empty($trainInfo) || !is_array($trainInfo)){
            return 0;
        }
        $trainCode = 0;
        $trainNumber = strtoupper($trainNumber);
//        vbot('console')->log($trainNumber);
        foreach ($trainInfo as $item) {
            if($item['ticket_no'] == $trainNumber){
                $trainCode = $item['train_code'];
                break;
            }
        }
//        vbot('console')->log($trainCode);
        return $trainCode;
    }

    //获取车站代码
    private function getStationCode($station, $type = 0)
    {
        if(empty($station)){
            return 0;
        }

        switch ($type){
            case 0:
                $type = 'name';
                break;
            case 1:
                $type = 'short';
                break;
            case 2:
                $type = 'pinyin';
                break;
        }

        $path = realpath(vbot("config")['path']). "/train/";
        $filename = $path."train_station.json";
        if(!is_file($filename)){
            return 0;
        }

        $info = file_get_contents($filename);
        $info = json_decode($info, true);

        $code = 0;
        foreach ($info as $item) {
            if($station == $item[$type]){
                $code = $item['code'];
                break;
            }
        }
        return $code;
    }
}