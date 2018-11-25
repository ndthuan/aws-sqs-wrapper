<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Subscribing\Callbacks;

use Ndthuan\AwsSqsWrapper\Queue\ReceivedMessage;
use Ndthuan\AwsSqsWrapper\Subscribing\Exception\FatalException;
use Ndthuan\AwsSqsWrapper\Subscribing\Exception\LogicException;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class LoggingCallbacks
 */
class LoggingCallbacks implements SubscriberCallbacksInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * LoggingCallbacks constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ReceivedMessage $message
     */
    public function onMessageReceived(ReceivedMessage $message)
    {
        $this->logger->info('Received SQS message', ['messageId' => $message->getId()]);
    }

    /**
     * @param ReceivedMessage $message
     */
    public function onMessageProcessed(ReceivedMessage $message)
    {
        $this->logger->info('Successfully processed SQS message', [
            'messageId' => $message->getId(),
        ]);
    }

    /**
     * @param ReceivedMessage $message
     * @param LogicException $exception
     */
    public function onLogicException(ReceivedMessage $message, LogicException $exception)
    {
        $this->logger->debug('Deleted SQS message due to logical exception', [
            'messageId' => $message->getId(),
            'exception' => $exception,
        ]);
    }

    /**
     * @param ReceivedMessage $message
     * @param FatalException $exception
     */
    public function onFatalException(ReceivedMessage $message, FatalException $exception)
    {
        $this->logger->critical('Stopped SQS processing due to fatal exception', [
            'messageId' => $message->getId(),
            'exception' => $exception,
        ]);
    }

    /**
     * @param ReceivedMessage $message
     * @param Throwable $exception
     */
    public function onUncaughtException(ReceivedMessage $message, Throwable $exception)
    {
        $this->logger->error('Uncaught exception when processing SQS message', [
            'messageId' => $message->getId(),
            'exception' => $exception,
        ]);
    }
}
