<?php

namespace QSOFT\Intranet\Rabbitmq\Services;

use QSOFT\Intranet\Rabbitmq\Amqp\Channel;
use QSOFT\Intranet\Rabbitmq\Amqp\SingleChannelStore;
use QSOFT\Intranet\Rabbitmq\Exceptions\AmqpException;

class Publisher
{
    protected string $exchange;

    protected Channel $channel;

    /**
     * @throws AmqpException
     */
    public function __construct(string $exchange = "", ?Channel $channel = null)
    {
        $this->exchange = $exchange;
        $channel ??= SingleChannelStore::get();
        $this->channel = $channel;
    }

    /**
     * @throws AmqpException
     */
    public function publish(string $routingKey, string $bytes)
    {
        $this->channel->publish($routingKey, $bytes, $this->exchange);
    }
}
