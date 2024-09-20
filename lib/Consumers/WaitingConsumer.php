<?php

namespace QSOFT\Intranet\Rabbitmq\Consumers;

use QSOFT\Intranet\Rabbitmq\Exceptions\AmqpException;

class WaitingConsumer extends AbstractConsumer
{
    /**
     * @throws AmqpException
     */
    public function run()
    {
        $this->registerSelf();
        $this->channel->declareQueue($this->queue);
        $this->channel->runWaitingLoop();
    }
}
