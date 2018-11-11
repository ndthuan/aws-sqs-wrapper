<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Subscribing\Exception;

use Ndthuan\AwsSqsWrapper\Subscribing\SubscribingException;

/**
 * Class LogicException
 *
 * Thrown when a logic exception occurs, subscriber should delete the message and should not bubble up this exception.
 */
class LogicException extends SubscribingException
{

}
