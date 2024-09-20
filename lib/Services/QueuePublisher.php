<?php

namespace QSOFT\Intranet\Rabbitmq\Services;

use QSOFT\Intranet\Rabbitmq\Amqp\SingleChannelStore;
use QSOFT\Intranet\Rabbitmq\Exceptions\AmqpException;

/**
 * Позволяет отправлять сообщения в заданную очередь через exchange по умолчанию
 */
class QueuePublisher
{
    protected string $queue;

    public function __construct(string $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @throws AmqpException
     */
    public function declareQueue(): self
    {
        SingleChannelStore::get()->declareQueue($this->queue);
        return $this;
    }

    /**
     * @throws AmqpException
     */
    public function publish(string $bytes)
    {
        SingleChannelStore::get()->publish($this->queue, $bytes);
    }
}
