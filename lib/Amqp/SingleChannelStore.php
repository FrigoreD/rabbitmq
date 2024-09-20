<?php

namespace QSOFT\Intranet\Rabbitmq\Amqp;

use QSOFT\Intranet\Rabbitmq\Exceptions\AmqpException;

class SingleChannelStore
{
    private static Channel $channel;

    /**
     * @throws AmqpException
     */
    public static function get(): Channel
    {
        self::$channel ??= ChannelFactory::getInstance()->makeChannel();
        return self::$channel;
    }
}
