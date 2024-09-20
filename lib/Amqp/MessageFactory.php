<?php

namespace QSOFT\Intranet\Rabbitmq\Amqp;

use PhpAmqpLib\Message\AMQPMessage;

class MessageFactory
{
    protected static AMQPMessage $messageTemplate;

    public function make(string $bytes): AMQPMessage
    {
        // Сообщения отличаются только контентом, поэтому переиспользуем одно и то же
        static::$messageTemplate ??= $this->makeExample();
        static::$messageTemplate->setBody($bytes);
        return static::$messageTemplate;
    }

    protected function makeExample(): AMQPMessage
    {
        return new AMQPMessage("", [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);
    }
}
