<?php

namespace QSOFT\Intranet\Rabbitmq\Consumers;

use QSOFT\Intranet\Rabbitmq\Amqp\Channel;
use QSOFT\Intranet\Rabbitmq\Amqp\Message;
use QSOFT\Intranet\Rabbitmq\Exceptions\AmqpException;
use QSOFT\Intranet\Rabbitmq\Helpers\Events;
use QSOFT\Intranet\Rabbitmq\Logger\Logger;
use Unilever\Logger\Log;
use Unilever\Logger\LogHelper;

abstract class AbstractMultiqueueConsumer
{
    /**
     * @var string[]
     */
    protected array $consumerTags = [];

    /**
     * @var string[]
     */
    protected array $queues;

    protected Channel $channel;

    abstract public function run();

    public function __construct(Channel $channel, array $queues)
    {
        $this->queues = $queues;
        $this->channel = $channel;
    }

    protected function registerSelf(): void
    {
        foreach ($this->queues as $queue) {
            try {
                $consumerTag = $this->channel->registerMessageHandler(
                    $queue,
                    function (Message $message, string $queue): bool {
                        return $this->handleMessage($message, $queue);
                    }
                );
                $this->consumerTags[] = $consumerTag;
            } catch (\Throwable $e) {
                Logger::error(
                    "Ошибка при регистрации консюьмера для очереди {$queue}: " . $e->getMessage()
                );
            }
        }
    }

    protected function handleMessage(Message $message, string $queue): bool
    {
//        Logger::info("В очереди $queue получено сообщение: " . $message->getBody());

        try {
            $this->sendEvents($message, $queue);
            return true;
        } catch (\Throwable $e) {

            (new Log('qsoft.elasticsearch.rabbitMq.request.handler'))->error(
                'Ошибка при выполнении обработчиков',
                LogHelper::getContextByException($e)
            );


/*            Logger::error(
                "Ошибка при выполнении обработчиков: "
                . $e->getMessage() . ' /// ' . $e->getTraceAsString()
            );*/

            return false;
        }
    }

    protected function sendEvents(Message $message, string $queue)
    {
        Events::sendMessageReceived($queue, $message);
        Events::sendMessageReceivedOnQueue($queue, $message);
    }

    /**
     * @throws AmqpException
     */
    public function declareQueues(): self
    {
        foreach ($this->queues as $queue) {
            $this->channel->declareQueue($queue);
        }
        return $this;
    }

    public function __destruct()
    {
        foreach ($this->consumerTags as $consumerTag) {
            try {
                if ($consumerTag) {
                    $this->channel->cancelConsumer($consumerTag);
                }
            } catch (\Throwable $e) {
            }
        }
    }
}
