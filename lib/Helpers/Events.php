<?php

namespace QSOFT\Intranet\Rabbitmq\Helpers;

use Bitrix\Main\Event;
use QSOFT\Intranet\Rabbitmq\Admin\Options;
use QSOFT\Intranet\Rabbitmq\Amqp\Message;

class Events
{
    public static function sendMessageReceived(string $queue, Message $message)
    {
        $event = new Event(
            Options::MODULE_ID,
            "OnMessageReceived",
            [
                'bytes' => $message->getBody(),
                'headers' => $message->getHeaders(),
                'queue' => $queue
            ]
        );
        $event->send();
    }

    public static function sendMessageReceivedOnQueue(string $queue, Message $message)
    {
        $event = new Event(
            Options::MODULE_ID,
            "OnMessageReceived_" . $queue,
            [
                'bytes' => $message->getBody(),
                'headers' => $message->getHeaders(),
            ]
        );
        $event->send();
    }

    public static function sendMessageNacked(Message $message)
    {
        $event = new Event(
            Options::MODULE_ID,
            "OnMessageNacked",
            [
                'bytes' => $message->getBody(),
                'headers' => $message->getHeaders(),
            ]
        );
        $event->send();
    }
}
