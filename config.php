<?php

$rootPath = __DIR__ . "/";
$path = $rootPath.'tmp/';

return [
    'path'     => $path,
    /*
     * 下载配置项
     */
    'download' => [
        'image'         => true,
        'voice'         => true,
        'video'         => true,
        'emoticon'      => true,
        'file'          => true,
        'emoticon_path' => $path.'emoticons',
    ],
    /*
     * 输出配置项
     */
    'console' => [
        'output'  => true, // 是否输出
        'message' => true, // 是否输出接收消息 （若上面为 false 此处无效）
    ],
    /*
     * 日志配置项
     */
    'log'      => [
        'level'         => 'debug',
        'permission'    => 0777,
        'system'        => $path.'log',
        'message'       => $path.'log',
    ],
    /*
     * 缓存配置项
     */
    'cache' => [
        'default' => 'file',
        'stores'  => [
            'file' => [
                'driver' => 'file',
                'path'   => $path.'cache',
            ],
            'redis' => [
                'driver'     => 'redis',
                'connection' => 'default',
            ],
        ],
    ],
    'database' => [
        'redis' => [
            'client'  => 'predis',
            'default' => [
                'host'     => '127.0.0.1',
                'password' => null,
                'port'     => 6379,
                'database' => 13,
            ],
        ],
        'sqlite' => [
            'file' => $rootPath."db/weather.db"
        ]
    ],
    'leancloud' =>[
        'appid'     =>  'FgMq6U5zwVtBecqj94rOG9Ot-gzGzoHsz',
        'appkey'    => 'vmmcyEE3ucEvpbtDOlG6uH1P',
        'masterkey' =>  'tCtcLw1fKowUmTc2sSRWpti7',
    ]
];
