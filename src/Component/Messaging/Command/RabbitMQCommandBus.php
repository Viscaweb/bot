<?php
namespace Visca\Bot\Component\Messaging\Command;

use SimpleBus\Message\Bus\MessageBus;
use SimpleBus\RabbitMQBundleBridge\RabbitMQPublisher;

final class RabbitMQCommandBus implements MessageBus
{
    /** @var RabbitMQPublisher */
    private $publisher;

    public function __construct(RabbitMQPublisher $publisher)
    {
        $this->publisher = $publisher;
    }

    public function handle($message)
    {
        $this->publisher->publish($message);
    }
}
