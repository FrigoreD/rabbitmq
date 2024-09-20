<?php

namespace QSOFT\Intranet\Rabbitmq\Consumers;

use QSOFT\Intranet\Rabbitmq\Exceptions\AmqpException;

class TimedMultiqueueConsumer extends AbstractMultiqueueConsumer
{
    protected int $timeLimitS = 55;

    /**
     * @throws AmqpException
     */
    public function run()
    {
        $this->registerSelf();
        $this->channel->runWaitingLoop($this->timeLimitS);
    }

    public function setTimeLimit(int $seconds): self
    {
        $this->timeLimitS = $seconds;
        return $this;
    }
}
