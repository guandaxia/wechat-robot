<?php

namespace Guandaxia;

use Hanson\Vbot\Foundation\Vbot;

class VbotHandler
{
    private $config;

    public function __construct($session = null)
    {
        $this->config = require_once __DIR__ . '/config.php';

        if ($session) {
            $this->config['session'] = $session;
        }
    }

    public function run()
    {
        $robot = new Vbot($this->config);

        $robot->messageHandler->setHandler([MessageHandler::class, 'messageHandler']);

        $robot->messageHandler->setCustomHandler([CustromHandler::class, 'custromHandler']);

        $robot->observer->setQrCodeObserver([Observer::class, 'setQrCodeObserver']);

        $robot->observer->setLoginSuccessObserver([Observer::class, 'setLoginSuccessObserver']);

        $robot->observer->setReLoginSuccessObserver([Observer::class, 'setReLoginSuccessObserver']);

        $robot->observer->setExitObserver([Observer::class, 'setExitObserver']);

        $robot->observer->setFetchContactObserver([Observer::class, 'setFetchContactObserver']);

        $robot->observer->setBeforeMessageObserver([Observer::class, 'setBeforeMessageObserver']);

        $robot->observer->setNeedActivateObserver([Observer::class, 'setNeedActivateObserver']);

        $robot->server->serve();
    }
}
