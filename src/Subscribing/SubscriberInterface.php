<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Subscribing;

use Ndthuan\AwsSqsWrapper\Subscribing\Exception\FatalException;
use Throwable;

/**
 * Interface SubscriberInterface
 */
interface SubscriberInterface
{
    /**
     * @throws FatalException
     * @throws Throwable
     */
    public function pullAndProcessMessages();
}
