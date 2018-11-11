<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Subscribing;

use Ndthuan\AwsSqsWrapper\Queue\ReceivedMessage;
use Ndthuan\AwsSqsWrapper\Queue\ResultMetadata;
use Ndthuan\AwsSqsWrapper\Subscribing\Exception\FatalException;
use Ndthuan\AwsSqsWrapper\Subscribing\Exception\LogicException;

/**
 * Interface MessageProcessorInterface
 */
interface MessageProcessorInterface
{
    /**
     * @param ReceivedMessage $receivedMessage
     * @param ResultMetadata  $resultMetadata
     *
     * @throws LogicException
     * @throws FatalException
     */
    public function processMessage(ReceivedMessage $receivedMessage, ResultMetadata $resultMetadata);
}
