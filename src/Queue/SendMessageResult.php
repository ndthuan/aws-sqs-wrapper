<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Queue;

use Aws\Result;

/**
 * Class SendMessageResult
 */
class SendMessageResult
{
    /**
     * @var string
     */
    private $messageBodyMd5;

    /**
     * @var string
     */
    private $messageAttributesMd5;

    /**
     * @var string
     */
    private $messageId;

    /**
     * @var ResultMetadata
     */
    private $metadata;

    /**
     * @param Result $result
     *
     * @return SendMessageResult
     */
    public static function fromAwsResult(Result $result): SendMessageResult
    {
        return new self(
            $result->get('MD5OfMessageBody') ?? '',
            $result->get('MD5OfMessageAttributes') ?? '',
            $result->get('MessageId') ?? '',
            ResultMetadata::fromArray($result->get('@metadata') ?? [])
        );
    }

    /**
     * SendMessageResult constructor.
     *
     * @param string         $messageBodyMd5
     * @param string         $messageAttributesMd5
     * @param string         $messageId
     * @param ResultMetadata $metadata
     */
    public function __construct(
        string $messageBodyMd5,
        string $messageAttributesMd5,
        string $messageId,
        ResultMetadata $metadata
    ) {
        $this->messageBodyMd5       = $messageBodyMd5;
        $this->messageAttributesMd5 = $messageAttributesMd5;
        $this->messageId            = $messageId;
        $this->metadata             = $metadata;
    }

    /**
     * @return string
     */
    public function getMessageBodyMd5(): string
    {
        return $this->messageBodyMd5;
    }

    /**
     * @return string
     */
    public function getMessageAttributesMd5(): string
    {
        return $this->messageAttributesMd5;
    }

    /**
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->messageId;
    }

    /**
     * @return ResultMetadata
     */
    public function getMetadata(): ResultMetadata
    {
        return $this->metadata;
    }
}
