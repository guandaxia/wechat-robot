<?php

namespace Guandaxia;

require __DIR__ . '/Loader.php';
//注册自动加载
Loader::register();

$vbot = new VbotHandler();
$vbot->run();


