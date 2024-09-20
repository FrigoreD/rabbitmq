<?php

namespace QSOFT\Intranet\Rabbitmq\Logger;

class Foobar
{
    public static function handle(\Bitrix\Main\Event $event)
    {
        dd($event);

        file_put_contents(\Bitrix\Main\Application::getDocumentRoot() . '/local/log.txt', print_r([
            'message' => $event->getParameters(),
            ], true) . PHP_EOL, FILE_APPEND);

//        if ($event->getParameters()['bytes']) {
//            // обработать сообщение
//
//
//        } else {
//
//
//            // вернуть собщение в очередь
//            return false;
//        }
    }
}
