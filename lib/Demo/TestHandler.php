<?php

namespace QSOFT\Intranet\Rabbitmq\Demo;

use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use QSOFT\Intranet\Rabbitmq\Admin\Options;

/**
 * Примеры работы с событиями модуля
 */
class TestHandler
{
    public static function register(): void
    {
        EventManager::getInstance()->registerEventHandler(
            Options::MODULE_ID,
            'OnMessageReceived',
            Options::MODULE_ID,
            TestHandler::class,
            'handle'
        );
    }

    public static function registerForQueue(string $queue): void
    {
        EventManager::getInstance()->registerEventHandler(
            Options::MODULE_ID,
            'OnMessageReceived_' . $queue,
            Options::MODULE_ID,
            TestHandler::class,
            'handle'
        );
    }

    public static function handle(Event $event)
    {
        \Bitrix\Main\Diag\Debug::writeToFile(func_get_args(), "EVENT_TRIGGERED");
    }
}
