<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Subscribing\Callbacks;

use Ndthuan\AwsSqsWrapper\Queue\ReceivedMessage;
use Ndthuan\AwsSqsWrapper\Subscribing\Exception\FatalException;
use Ndthuan\AwsSqsWrapper\Subscribing\Exception\LogicException;
use Throwable;

/**
 * Interface SubscriberCallbacksInterface
 *
 * Subscriber callback methods.
 */
interface SubscriberCallbacksInterface
{
    /**
     * @param ReceivedMessage $message
     */
    public function onMessageReceived(ReceivedMessage $message);

    /**
     * @param ReceivedMessage $message
     */
    public function onMessageProcessed(ReceivedMessage $message);

    /**
     * @param ReceivedMessage $message
     * @param LogicException $exception
     */
    public function onLogicException(ReceivedMessage $message, LogicException $exception);

    /**
     * @param ReceivedMessage $message
     * @param FatalException $exception
     */
    public function onFatalException(ReceivedMessage $message, FatalException $exception);

    /**
     * @param ReceivedMessage $message
     * @param Throwable $exception
     */
    public function onUncaughtException(ReceivedMessage $message, Throwable $exception);
}
