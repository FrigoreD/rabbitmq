<?php

namespace QSOFT\Intranet\Rabbitmq\Services;

use QSOFT\Intranet\Rabbitmq\Admin\Options;
use QSOFT\Intranet\Rabbitmq\Exceptions\AmqpException;

/**
 * Позволяет отправлять сообщения в выходную очередь, установленную в настройках модуля, через exchange по умолчанию
 */
class SimplePublisher
{
    protected QueuePublisher $publisher;

    /**
     * @throws AmqpException
     */
    public function __construct()
    {
        $this->publisher = new QueuePublisher(Options::getOutputQueue());
        $this->publisher->declareQueue();
    }

    /**
     * @throws AmqpException
     */
    public function publish(string $bytes)
    {
        $this->publisher->publish($bytes);
    }
}
