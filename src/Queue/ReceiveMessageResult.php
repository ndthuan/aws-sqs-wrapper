<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Queue;

use function array_map;
use Aws\Result;

/**
 * Class ReceiveMessageResult
 */
class ReceiveMessageResult
{
    /**
     * @var array
     */
    private $messages;

    /**
     * @var ResultMetadata
     */
    private $metadata;

    /**
     * @param Result $result
     *
     * @return ReceiveMessageResult
     */
    public static function fromAwsResult(Result $result): ReceiveMessageResult
    {
        $messageObjects = array_map(function (array $message) {
            return Message::fromArray($message);
        }, $result->get('Messages'));

        return new self($messageObjects, ResultMetadata::fromArray($result->get('@metadata')));
    }

    /**
     * ReceiveResult constructor.
     *
     * @param Message[]      $messages
     * @param ResultMetadata $metadata
     */
    public function __construct(array $messages, ResultMetadata $metadata)
    {
        $this->messages = $messages;
        $this->metadata = $metadata;
    }

    /**
     * @return Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return ResultMetadata
     */
    public function getMetadata(): ResultMetadata
    {
        return $this->metadata;
    }
}
