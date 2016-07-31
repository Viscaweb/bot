<?php
namespace Visca\Bot\Component\GitHub\Event\Event;

use JMS\Serializer\Annotation\Type;

/**
 * Class WebHookReceivedEvent
 */
final class WebHookReceivedEvent
{
    /**
     * @var array
     * @Type("array")
     */
    private $headers;

    /**
     * @var array
     * @Type("array")
     */
    private $payload;

    /**
     * ProcessWebHookCommand constructor.
     *
     * @param array $headers
     * @param array $payload
     */
    public function __construct(array $headers, array $payload)
    {
        $this->headers = $headers;
        $this->payload = $payload;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }
}
