<?php

namespace QSOFT\Intranet\Rabbitmq\Amqp;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class Message
{
    protected AMQPMessage $message;

    public function __construct(AMQPMessage $message)
    {
        $this->message = $message;
    }

    public function getBody(): string
    {
        return $this->message->getBody();
    }

    public function getDeliveryTag(): int
    {
        return $this->message->getDeliveryTag();
    }

    public function getHeaders(): array
    {
        $properties = $this->message->get_properties();

        if (isset($properties['application_headers']) && $properties['application_headers'] instanceof AMQPTable) {
            return $properties['application_headers']->getNativeData();
        } else {
            return [];
        }
    }

    /**
     * @return array - служебные заголовки сообщения (за исключением кастомных свойств)
     */
    public function getProperties(): array
    {
        $properties = $this->message->get_properties();
        unset($properties['application_headers']);
        return $properties;
    }

    public function getRoutingKey(): ?string
    {
        return $this->message->getRoutingKey();
    }
}
