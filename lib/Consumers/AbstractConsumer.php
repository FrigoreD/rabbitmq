<?php

namespace QSOFT\Intranet\Rabbitmq\Consumers;

use Bitrix\Main\Event;
use QSOFT\Intranet\Rabbitmq\Admin\Options;
use QSOFT\Intranet\Rabbitmq\Amqp\Channel;
use QSOFT\Intranet\Rabbitmq\Amqp\Message;
use QSOFT\Intranet\Rabbitmq\Exceptions\AmqpException;
use QSOFT\Intranet\Rabbitmq\Logger\Logger;
use Unilever\Logger\Log;
use Unilever\Logger\LogHelper;

abstract class AbstractConsumer
{
    protected string $consumerTag;

    protected string $queue;

    protected Channel $channel;

    abstract public function run();

    public function __construct(Channel $channel, string $queue)
    {
        $this->queue = $queue;
        $this->channel = $channel;
    }

    /**
     * @throws AmqpException
     */
    protected function registerSelf(): void
    {
        $this->consumerTag = $this->channel->registerMessageHandler(
            $this->queue,
            function (Message $message, string $queue): bool {
                return $this->handleMessage($message, $queue);
            }
        );
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
        $event = new Event(
            Options::MODULE_ID,
            "OnMessageReceived",
            [
                'bytes' => $message->getBody(),
                'queue' => $queue
            ]
        );
        $event->send();

        $event = new Event(
            Options::MODULE_ID,
            "OnMessageReceived_" . $queue,
            [
                'bytes' => $message->getBody(),
            ]
        );
        $event->send();
    }

    /**
     * @throws AmqpException
     */
    public function declareQueue(): self
    {
        $this->channel->declareQueue($this->queue);
        return $this;
    }

    public function __destruct()
    {
        try {
            if ($this->consumerTag) {
                $this->channel->cancelConsumer($this->consumerTag);
            }
        } catch (\Throwable $e) {
        }
    }
}
