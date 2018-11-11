<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Publishing;

use JsonSerializable;
use function json_encode;
use Ndthuan\AwsSqsWrapper\Queue\Connector;
use Ndthuan\AwsSqsWrapper\Queue\MessageAttributes;
use Ndthuan\AwsSqsWrapper\Queue\SendMessageResult;

/**
 * Class Publisher
 *
 * Message sender.
 */
class Publisher
{
    /**
     * @var Connector
     */
    private $connector;

    /**
     * Publisher constructor.
     *
     * @param Connector $connector
     */
    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * @param string                 $messageBody
     * @param array                  $additionalParams
     * @param MessageAttributes|null $attributes
     *
     * @return SendMessageResult
     */
    public function publishString(
        string $messageBody,
        array $additionalParams = [],
        MessageAttributes $attributes = null
    ): SendMessageResult {
        return $this->connector->sendMessage($messageBody, $additionalParams, $attributes);
    }

    /**
     * @param JsonSerializable|array $jsonSerializable
     * @param array                  $additionalParams
     * @param MessageAttributes|null $attributes
     *
     * @return SendMessageResult
     */
    public function publishJsonSerializable(
        $jsonSerializable,
        array $additionalParams = [],
        MessageAttributes $attributes = null
    ): SendMessageResult {
        return $this->publishString(json_encode($jsonSerializable), $additionalParams, $attributes);
    }
}
