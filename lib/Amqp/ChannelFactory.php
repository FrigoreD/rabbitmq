<?php

namespace QSOFT\Intranet\Rabbitmq\Amqp;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use QSOFT\Intranet\Rabbitmq\Admin\Options;
use QSOFT\Intranet\Rabbitmq\Exceptions\AmqpException;
use QSOFT\Intranet\Rabbitmq\Logger\Logger;

class ChannelFactory
{
    // Это чтобы задать channel_rpc_timeout
    protected const CONNECTION_DEFAULT_ARGS = [
        'host' => null,
        'port' => null,
        'user' => null,
        'password' => null,
        'vhost' => null,
        'insist' => false,
        'login_method' => 'AMQPLAIN',
        'login_response' => null,
        'locale' => 'en_US',
        'connection_timeout' => 3.0,
        'read_write_timeout' => 3.0,
        'context' => null,
        'keepalive' => false,
        'heartbeat' => 0,
        'channel_rpc_timeout' => 0.0
    ];

    protected static self $instance;

    protected AMQPStreamConnection $connection;

    /**
     * @throws AmqpException
     */
    protected function __construct()
    {
        $this->connection = $this->getNewConnection();
    }

    public static function getInstance(): self
    {
        static::$instance ??= new static();
        return static::$instance;
    }

    /**
     * @throws AmqpException
     */
    public function makeChannel(): Channel
    {
        return new Channel($this->connection->channel());
    }

    /**
     * @throws AmqpException
     */
    private function getNewConnection(): AMQPStreamConnection
    {
        $parameters = Options::getConnectionParameters();
        $args = static::CONNECTION_DEFAULT_ARGS;
        $args = array_replace($args, $parameters);
        $args['channel_rpc_timeout'] = (float)Options::getChannelTimeoutS();
        // read_write_timeout не может быть меньше channel_rpc_timeout
        $args['read_write_timeout'] = $args['channel_rpc_timeout'] * 2;

        try {
            return new AMQPStreamConnection(
                ...array_values($args)
            );
        } catch (\Exception $e) {
            $message = 'Ошибка при установке соединения в RabbitMQ: ' . $e->getMessage();
            Logger::error($message);
            throw new AmqpException($message, 0, $e);
        }
    }

    public function __destruct()
    {
        try {
            if ($this->connection->isConnected()) {
                $this->connection->close();
            }
        } catch (\Throwable $e) {
        }
    }
}
