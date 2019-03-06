<?php

namespace Guandaxia;

use think\Db;

require __DIR__ . '/Loader.php';
//注册自动加载
Loader::register();

$vbot = new VbotHandler();

Db::setConfig([
    // 数据库类型
    'type' => 'mysql',
    // 服务器地址
    'hostname' => '127.0.0.1',
    // 数据库名
    'database' => 'vbot',
    // 数据库用户名
    'username' => 'homestead',
    // 数据库密码
    'password' => 'secret',
    // 数据库连接端口
    'hostport' => '33060',
    // 数据库连接参数
    'params' => [],
    // 数据库编码默认采用utf8
    'charset' => 'utf8',

    'debug'           => true,
]);

$vbot->run();


