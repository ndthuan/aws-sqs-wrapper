<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Queue;

/**
 * Class Message
 *
 * Represents an SQS message.
 */
class Message
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $receiptHandle;

    /**
     * @var string
     */
    private $bodyHash;

    /**
     * @var string
     */
    private $body;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @param array $message
     *
     * @return Message
     */
    public static function fromArray(array $message): Message
    {
        return new self(
            $message['MessageId'] ?? '',
            $message['ReceiptHandle'] ?? '',
            $message['MD5OfBody'] ?? '',
            $message['Body'] ?? '',
            $message['Attributes'] ?? []
        );
    }

    /**
     * Message constructor.
     *
     * @param string $id
     * @param string $receiptHandle
     * @param string $bodyHash
     * @param string $body
     * @param array  $attributes
     */
    public function __construct(string $id, string $receiptHandle, string $bodyHash, string $body, array $attributes)
    {
        $this->id            = $id;
        $this->receiptHandle = $receiptHandle;
        $this->bodyHash      = $bodyHash;
        $this->body          = $body;
        $this->attributes    = $attributes;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getReceiptHandle(): string
    {
        return $this->receiptHandle;
    }

    /**
     * @return string
     */
    public function getBodyHash(): string
    {
        return $this->bodyHash;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
