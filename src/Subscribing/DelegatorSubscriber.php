<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Subscribing;

use Ndthuan\AwsSqsWrapper\Queue\Connector;
use Ndthuan\AwsSqsWrapper\Queue\ReceivedMessage;
use Ndthuan\AwsSqsWrapper\Queue\ResultMetadata;
use Ndthuan\AwsSqsWrapper\Subscribing\Callbacks\SubscriberCallbacksInterface;
use Ndthuan\AwsSqsWrapper\Subscribing\Exception\ValidationFailureException;

/**
 * Class DelegatorSubscriber
 *
 * A subscriber that delegates message processing to a separate message processor.
 */
class DelegatorSubscriber extends AbstractSubscriber
{
    /**
     * @var MessageProcessorInterface
     */
    private $messageProcessor;

    /**
     * DelegatorSubscriber constructor.
     *
     * @param MessageProcessorInterface         $messageProcessor
     * @param Connector                         $queueConnector
     * @param array                             $receiveMessageOptions
     * @param SubscriberCallbacksInterface|null $callbacks
     */
    public function __construct(
        MessageProcessorInterface $messageProcessor,
        Connector $queueConnector,
        array $receiveMessageOptions = [],
        SubscriberCallbacksInterface $callbacks = null
    ) {
        parent::__construct($queueConnector, $receiveMessageOptions, $callbacks);

        $this->messageProcessor = $messageProcessor;
    }

    /**
     * @inheritdoc
     *
     * @throws ValidationFailureException
     */
    public function processMessage(ReceivedMessage $receivedMessage, ResultMetadata $resultMetadata)
    {
        if ($this->messageProcessor instanceof MessageValidatorInterface) {
            $this->messageProcessor->validateMessage($receivedMessage);
        }

        $this->messageProcessor->processMessage($receivedMessage, $resultMetadata);
    }
}
