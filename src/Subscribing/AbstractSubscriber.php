<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Subscribing;

use Ndthuan\AwsSqsWrapper\Queue\Connector;
use Ndthuan\AwsSqsWrapper\Queue\ReceivedMessage;
use Ndthuan\AwsSqsWrapper\Queue\ResultMetadata;
use Ndthuan\AwsSqsWrapper\Subscribing\Callbacks\SubscriberCallbacksInterface;
use Ndthuan\AwsSqsWrapper\Subscribing\Exception\FatalException;
use Ndthuan\AwsSqsWrapper\Subscribing\Exception\LogicException;
use Throwable;

/**
 * Class AbstractSubscriber
 */
abstract class AbstractSubscriber implements SubscriberInterface, MessageProcessorInterface
{
    /**
     * @var Connector
     */
    private $queueConnector;

    /**
     * @var SubscriberCallbacksInterface
     */
    private $callbacks;

    /**
     * @var array
     */
    private $receiveMessageOptions;

    /**
     * AbstractSubscriber constructor.
     *
     * @param Connector $queueConnector
     * @param SubscriberCallbacksInterface $callbacks
     * @param array $receiveMessageOptions
     */
    public function __construct(
        Connector $queueConnector,
        SubscriberCallbacksInterface $callbacks,
        array $receiveMessageOptions = []
    ) {
        $this->queueConnector        = $queueConnector;
        $this->callbacks             = $callbacks;
        $this->receiveMessageOptions = $receiveMessageOptions;
    }

    /**
     * @throws Throwable
     */
    public function pullAndProcessMessages()
    {
        $receiveResult = $this->queueConnector->receiveMessage($this->receiveMessageOptions);

        foreach ($receiveResult->getMessages() as $message) {
            $this->callbacks->onMessageReceived($message);

            try {
                $this->processMessage($message, $receiveResult->getMetadata());
                $this->queueConnector->deleteMessage($message->getReceiptHandle());

                $this->callbacks->onMessageProcessed($message);
            } catch (LogicException $exception) {
                $this->queueConnector->deleteMessage($message->getReceiptHandle());

                $this->callbacks->onLogicException($message, $exception);
            } catch (FatalException $exception) {
                $this->callbacks->onFatalException($message, $exception);

                throw $exception;
            } catch (Throwable $exception) {
                $this->callbacks->onUncaughtException($message, $exception);
            }
        }
    }

    /**
     * @inheritdoc
     */
    abstract public function processMessage(ReceivedMessage $receivedMessage, ResultMetadata $resultMetadata);
}
