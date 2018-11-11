<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Subscribing;

use Ndthuan\AwsSqsWrapper\Queue\ReceivedMessage;
use Ndthuan\AwsSqsWrapper\Subscribing\Exception\ValidationFailureException;

/**
 * Interface MessageValidatorInterface
 */
interface MessageValidatorInterface
{
    /**
     * @param ReceivedMessage $receivedMessage
     *
     * @throws ValidationFailureException
     */
    public function validateMessage(ReceivedMessage $receivedMessage);
}
