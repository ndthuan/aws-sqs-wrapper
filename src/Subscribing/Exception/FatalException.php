<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Subscribing\Exception;

use Ndthuan\AwsSqsWrapper\Subscribing\SubscribingException;

/**
 * Class FatalException
 *
 * Thrown when a system failure occurs, subscriber should stop processing and bubble up this exception.
 */
class FatalException extends SubscribingException
{

}
