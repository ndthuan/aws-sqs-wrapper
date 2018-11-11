<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Subscribing;

use Ndthuan\AwsSqsWrapper\Queue\Connector;
use Ndthuan\AwsSqsWrapper\Queue\ReceivedMessage;
use Ndthuan\AwsSqsWrapper\Queue\ResultMetadata;
use Ndthuan\AwsSqsWrapper\Subscribing\Exception\FatalException;
use Ndthuan\AwsSqsWrapper\Subscribing\Exception\LogicException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

/**
 * Class AbstractSubscriber
 */
abstract class AbstractSubscriber implements SubscriberInterface, MessageProcessorInterface, LoggerAwareInterface
{
    /**
     * @var Connector
     */
    private $queueConnector;

    /**
     * @var array
     */
    private $receiveMessageOptions;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AbstractSubscriber constructor.
     *
     * @param Connector       $queueConnector
     * @param array           $receiveMessageOptions
     */
    public function __construct(
        Connector $queueConnector,
        array $receiveMessageOptions = []
    ) {
        $this->queueConnector        = $queueConnector;
        $this->receiveMessageOptions = $receiveMessageOptions;

        $this->setLogger(new NullLogger());
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws Throwable
     */
    public function pullAndProcessMessages()
    {
        $receiveResult = $this->queueConnector->receiveMessage($this->receiveMessageOptions);

        foreach ($receiveResult->getMessages() as $message) {
            $this->logger->info('Received SQS message', ['message' => $message]);

            try {
                $this->processMessage($message, $receiveResult->getMetadata());

                $this->queueConnector->deleteMessage($message->getReceiptHandle());

                $this->logger->info('Successfully processed SQS message', [
                    'message' => $message,
                ]);
            } catch (LogicException $exception) {
                $this->queueConnector->deleteMessage($message->getReceiptHandle());

                $this->logger->info('Deleted SQS message due to logical exception', [
                    'message' => $message,
                    'exception' => $exception,
                ]);
            } catch (FatalException $exception) {
                $this->logger->critical('Stopped SQS processing due to fatal exception', [
                    'message' => $message,
                    'exception' => $exception,
                ]);

                throw $exception;
            } catch (Throwable $exception) {
                $this->logger->error('Uncaught exception when processing SQS message', [
                    'message' => $message,
                    'exception' => $exception,
                ]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    abstract public function processMessage(ReceivedMessage $receivedMessage, ResultMetadata $resultMetadata);
}
