<?php

namespace QSOFT\Intranet\Rabbitmq\Amqp;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use QSOFT\Intranet\Rabbitmq\Admin\Options;
use QSOFT\Intranet\Rabbitmq\Exceptions\AmqpException;
use QSOFT\Intranet\Rabbitmq\Helpers\Events;
use QSOFT\Intranet\Rabbitmq\Logger\Logger;

class Channel
{
    protected AMQPChannel $channel;

    protected MessageFactory $messageFactory;

    /**
     * @throws AmqpException
     */
    public function __construct(AMQPChannel $channel)
    {
        $this->messageFactory = new MessageFactory();
        $this->channel = $channel;

        $this->registerPublisherNackHandler();
    }

    /**
     * @throws AmqpException
     */
    protected function registerPublisherNackHandler()
    {
        $this->execute('confirm_select');
        $this->execute('set_nack_handler', [
            function (AMQPMessage $message) {
                Logger::warning("Брокер отправил nack на сообщение " . $message->getBody());
                Events::sendMessageNacked(new Message($message));
            }
        ]);
    }

    public function __destruct()
    {
        try {
            $this->channel->wait_for_pending_acks(1);
            $this->channel->close();
        } catch (\Throwable $e) {
        }
    }

    /**
     * @throws AmqpException
     */
    public function publish(string $routingKey, string $bytes, string $exchange = '')
    {
        $message = $this->messageFactory->make($bytes);

        $this->execute('basic_publish', [
            $message,
            $exchange,
            $routingKey
        ]);
    }

    /**
     * @param string $name
     * @param bool $passive - если очередь уже существует, выбросить исключение
     * @param bool $durable - сохранится ли очередь при перезагрузке брокера
     * @param bool $exclusive - очередь доступна только в ТЕКУЩЕМ соединении и удаляется при его закрытии
     * @param bool $autoDelete - удалить очередь, когда последний консьюмер закончит работу
     * @throws AmqpException
     */
    public function declareQueue(
        string $name,
        bool $passive = false,
        bool $durable = true,
        bool $exclusive = false,
        bool $autoDelete = false
    ) {
        $this->execute('queue_declare', [
            $name,
            $passive,
            $durable,
            $exclusive,
            $autoDelete,
        ]);
    }

    /**
     * @param string $queueName
     * @param callable $callback - должен принимать два аргумента: Message и строку queue
     * @return string - consumer_tag
     * @throws AmqpException
     */
    public function registerMessageHandler(string $queueName, callable $callback): string
    {
        $shouldRequeue = Options::shouldRequeueOnFailure();

        $wrappedCallback = function (AMQPMessage $message) use ($callback, $queueName, $shouldRequeue) {
            if (call_user_func($callback, new Message($message), $queueName) !== false) {
                $message->ack();
            } else {
                $message->nack($shouldRequeue);
            }
        };

        return $this->execute('basic_consume', [
            $queueName,
            '',
            false,
            false,
            false,
            false,
            $wrappedCallback,
        ]);
    }

    public function cancelConsumer(string $consumerTag)
    {
        $this->channel->basic_cancel($consumerTag);
    }

    public function isOpen(): bool
    {
        return $this->channel->is_open();
    }

    /**
     * @throws AmqpException
     */
    public function runWaitingLoop(int $timeoutSeconds = 0)
    {
        $startTime = time();
        while ($this->execute('is_open')) {
            $secondsRemaining = $timeoutSeconds - (time() - $startTime);
            if ($secondsRemaining <= 0) {
                break;
            }

            try {
                // Не используем execute, чтобы обработать штатный таймаут
                $this->channel->wait(null, false, $timeoutSeconds);
            } catch (AMQPTimeoutException $e) {
                // Ожидание сообщений штатно заканчивается таймаутом, пропускаем
            } catch (\Exception $e) {
                $message = "Ошибка при вызове \$channel->wait: " . $e->getMessage();
                Logger::error($message);
                throw new AmqpException($message, 0, $e);
            }
        }
    }

    protected function execute(string $method, array $args = [])
    {
        try {
            return $this->channel->$method(...$args);
        } catch (\Exception $e) {
            $message = "Ошибка при вызове \$channel->$method: " . $e->getMessage();
            Logger::error($message);
            throw new AmqpException($message, 0, $e);
        }
    }
}
