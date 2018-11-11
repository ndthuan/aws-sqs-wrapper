<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Queue;

use Aws\Sqs\SqsClient;

/**
 * Class Connector
 *
 * For connecting to a queue and manipulating messages.
 */
class Connector
{
    /**
     * @var SqsClient
     */
    private $sqsClient;

    /**
     * @var string
     */
    private $queueUrl;

    /**
     * @var array
     */
    private $defaultReceiveMessageOptions;

    /**
     * Connector constructor.
     *
     * @param SqsClient $sqsClient
     * @param string    $queueUrl
     */
    public function __construct(SqsClient $sqsClient, string $queueUrl)
    {
        $this->sqsClient = $sqsClient;
        $this->queueUrl  = $queueUrl;

        $this->defaultReceiveMessageOptions = [
            'AttributeNames'        => ['SentTimestamp'],
            'MaxNumberOfMessages'   => 1,
            'MessageAttributeNames' => ['All'],
            'WaitTimeSeconds'       => 0,
        ];
    }

    /**
     * @param string                 $messageBody
     * @param array                  $additionalParams Where you put DelaySeconds, MessageDeduplicationId etc...
     * @param MessageAttributes|null $attributes
     *
     * @return SendMessageResult
     */
    public function sendMessage(
        string $messageBody,
        array $additionalParams = [],
        MessageAttributes $attributes = null
    ): SendMessageResult {
        $params = array_merge($additionalParams, [
            'MessageAttributes' => $attributes ? $attributes->toArray() : [],
            'MessageBody'       => $messageBody,
            'QueueUrl'          => $this->queueUrl,
        ]);

        $result = $this->sqsClient->sendMessage($params);

        return SendMessageResult::fromAwsResult($result);
    }

    /**
     * @param array $options
     *
     * @return ReceiveMessageResult
     */
    public function receiveMessage(array $options = []): ReceiveMessageResult
    {
        $options = $this->normalizeReceiveMessageOptions($options);

        $result = $this->sqsClient->receiveMessage($options);

        return ReceiveMessageResult::fromAwsResult($result);
    }

    /**
     * @param string $receiptHandle
     *
     * @return ResultMetadata
     */
    public function deleteMessage(string $receiptHandle): ResultMetadata
    {
        $result = $this->sqsClient->deleteMessage([
            'QueueUrl'      => $this->queueUrl,
            'ReceiptHandle' => $receiptHandle,
        ]);

        return ResultMetadata::fromArray($result->get('@metadata'));
    }

    /**
     * @param array $defaultReceiveMessageOptions
     */
    public function setDefaultReceiveMessageOptions(array $defaultReceiveMessageOptions)
    {
        $this->defaultReceiveMessageOptions = $defaultReceiveMessageOptions;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function normalizeReceiveMessageOptions(array $options): array
    {
        $options['QueueUrl'] = $this->queueUrl;

        foreach ($this->defaultReceiveMessageOptions as $key => $value) {
            if (! isset($options[$key])) {
                $options[$key] = $value;
            }
        }

        return $options;
    }
}
