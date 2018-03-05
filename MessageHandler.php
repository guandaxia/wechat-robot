<?php

namespace Guandaxia;

use Guandaxia\Handlers\Contact\BikeContact;
use Guandaxia\Handlers\Contact\BlessingContact;
use Guandaxia\Handlers\Contact\ChargeContact;
//use Guandaxia\Handlers\Contact\CheckUserContact;
use Guandaxia\Handlers\Contact\DaliyReportContact;
use Guandaxia\Handlers\Contact\ExpressContact;
use Guandaxia\Handlers\Contact\TrainContact;
use Guandaxia\Handlers\Contact\WeatherContact;
use Hanson\Vbot\Contact\Friends;
use Hanson\Vbot\Contact\Groups;
use Hanson\Vbot\Contact\Members;
//use Guandaxia\Handlers\Contact\ColleagueGroup;
//use Guandaxia\Handlers\Contact\ExperienceGroup;
//use Guandaxia\Handlers\Contact\FeedbackGroup;
//use Guandaxia\Handlers\Contact\Hanson;
//use Guandaxia\Handlers\Service\GuessNumber;
use Guandaxia\Handlers\Type\RecallType;
use Guandaxia\Handlers\Type\TextType;
use Hanson\Vbot\Message\Emoticon;
//use Hanson\Vbot\Message\File;
use Hanson\Vbot\Message\Image;
use Hanson\Vbot\Message\Text;
//use Hanson\Vbot\Message\Video;
//use Hanson\Vbot\Message\Voice;
use Illuminate\Support\Collection;

class MessageHandler
{
    public static function messageHandler(Collection $message)
    {
        /** @var Friends $friends */
        $friends = vbot('friends');

        /** @var Members $members */
//        $members = vbot('members');

        /** @var Groups $groups */
        $groups = vbot('groups');

//        if ($message['type'] === 'touch') {
//            Text::send($message['raw']['ToUserName'], $message['content']);
//        }

        if($message['type'] == 'text'){
//            FeedbackGroup::messageHandler($message, $friends, $groups);
//            ExperienceGroup::messageHandler($message, $friends, $groups);
            TrainContact::messageHandler($message, $friends, $groups);
            ExpressContact::messageHandler($message, $friends, $groups);
            BikeContact::messageHandler($message, $friends, $groups);
            ChargeContact::messageHandler($message, $friends, $groups);
            DaliyReportContact::messageHandler($message);
            WeatherContact::messageHandler($message, $friends, $groups);
            BlessingContact::messageHandler($message, $friends, $groups);
            TextType::messageHandler($message, $friends, $groups);
        }

//        GuessNumber::messageHandler($message);

        //表情
        if ($message['type'] === 'emoticon') {
            Emoticon::download($message);
//            Emoticon::send($message['from']['UserName'], $message);
//            Emoticon::sendRandom($message['from']['UserName']);
        }

        if ($message['type'] === 'location') {
            ChargeContact::messageHandler($message, $friends, $groups);
//            Text::send($message['from']['UserName'], $message['content']);
//            Text::send($message['from']['UserName'], $message['url']);
        }

        if ($message['type'] === 'new_friend') {
            Text::send($message['from']['UserName'], $message['content']);
        }

        if ($message['type'] === 'image') {
            Image::download($message);
//            Image::download($message, function ($resource) {
//                file_put_contents(__DIR__.'/test1.jpg', $resource);
//            });
//            Image::send($message['from']['UserName'], $message);
//          Image::send($message['from']['UserName'], __DIR__.'/test1.jpg');
        }

//        if ($message['type'] === 'voice') {
            //                Voice::download($message);
//                Voice::download($message, function ($resource) {
//                    file_put_contents(__DIR__.'/test1.mp3', $resource);
//                });
//            Voice::send($message['from']['UserName'], $message);
//                Voice::send($message['from']['UserName'], __DIR__.'/test1.mp3');
//        }

//        if ($message['type'] === 'video') {
            //                Video::download($message);
//                Video::download($message, function($resource){
//                    file_put_contents(__DIR__.'/test1.mp4', $resource);
//                });
//            Video::send($message['from']['UserName'], $message);
//                Video::send($message['from']['UserName'], __DIR__.'/test1.mp4');
//        }

        //撤回
        if ($message['type'] === 'recall') {
            RecallType::messageHandler($message);
        }

        if ($message['type'] === 'red_packet') {
            Text::send($message['from']['UserName'], $message['content']);
        }

        //转账
        if ($message['type'] === 'transfer') {
            Text::send($message['from']['UserName'], $message['content'].' 转账金额： '.$message['fee'].
                ' 转账流水号：'.$message['transaction_id'].' 备注：'.$message['memo']);
        }

//        if ($message['type'] === 'file') {
//            File::send($message['from']['UserName'], $message);
//            Text::send($message['from']['UserName'], '收到文件：'.$message['title']);
//        }

//        if ($message['type'] === 'mina') {
//            Text::send($message['from']['UserName'], '收到小程序：'.$message['title'].$message['url']);
//        }

        if ($message['type'] === 'share') {
            Text::send($message['from']['UserName'], '收到分享:'.$message['title'].$message['description'].
                $message['app'].$message['url']);
        }

//        if ($message['type'] === 'card') {
//            Text::send($message['from']['UserName'], '收到名片:'.$message['avatar'].$message['province'].
//                $message['city'].$message['description']);
//        }

//        if ($message['type'] === 'official') {
//            vbot('console')->log('收到公众号消息:'.$message['title'].$message['description'].
//                $message['app'].$message['url']);
//        }

        if ($message['type'] === 'request_friend') {
            vbot('console')->log('收到好友申请:'.$message['info']['Content'].$message['avatar']);
            if ($message['info']['Content'] === 'echo') {
                $friends->approve($message);
            }
        }
    }
}
