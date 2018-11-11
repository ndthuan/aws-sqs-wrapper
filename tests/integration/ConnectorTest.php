<?php
declare(strict_types=1);

namespace Ndthuan\Tests\AwsSqsWrapper\Integration;

use Aws\Sqs\SqsClient;
use Ndthuan\AwsSqsWrapper\Queue\Connector;
use Ndthuan\AwsSqsWrapper\Queue\ResultMetadata;
use PHPUnit\Framework\TestCase;

class ConnectorTest extends TestCase
{
    /**
     * @var SqsClient
     */
    private $sqsClient;

    protected function setUp()
    {
        parent::setUp();

        $this->sqsClient = new SqsClient([
            'region'      => 'fake',
            'version'     => 'latest',
            'credentials' => [
                'key'    => '', // doesn't matter with local stack
                'secret' => '',
            ],
        ]);
    }

    public function testFullFlow()
    {
        $queueUrl = 'http://localstack:4576/queue/test-queue';

        $connector = new Connector($this->sqsClient, $queueUrl);

        $sendResult = $connector->sendMessage('Xin chao');
        $this->assertNotEmpty($sendResult->getMessageId(), 'Sent message id must not be empty');
        $this->assertNotEmpty($sendResult->getMessageAttributesMd5(), 'Sent message attributes md5');
        $this->assertNotEmpty($sendResult->getMessageBodyMd5(), 'Sent message body md5');
        $this->assertSame(200, $sendResult->getMetadata()->getStatusCode(), 'Send result status should be 200');
        $this->assertSame($queueUrl, $sendResult->getMetadata()->getEffectiveUri(), 'Send result effective URI');
        $this->assertNotEmpty($sendResult->getMetadata()->getHeaders(), 'Send result headers not empty');
        $this->assertNotEmpty($sendResult->getMetadata()->getTransferStats(), 'Send result transfer stats not empty');

        $receiveResult = $connector->receiveMessage();
        $this->assertSame(200, $receiveResult->getMetadata()->getStatusCode(), 'Receive result status should be 200');
        $this->assertSame($queueUrl, $receiveResult->getMetadata()->getEffectiveUri(), 'Receive result effective URI');
        $this->assertNotEmpty($receiveResult->getMetadata()->getHeaders(), 'Receive result headers not empty');
        $this->assertNotEmpty(
            $receiveResult->getMetadata()->getTransferStats(),
            'Receive result transfer stats not empty'
        );
        $this->assertCount(1, $receiveResult->getMessages(), 'Received message count must be one');

        $receivedMessage = $receiveResult->getMessages()[0];
        $this->assertSame(
            $sendResult->getMessageId(),
            $receivedMessage->getId(),
            'Received message id must be the same as sent message id'
        );
        $this->assertSame('Xin chao', $receivedMessage->getBody(), 'Receive message body');
        $this->assertNotEmpty($receivedMessage->getBodyHash(), 'Received message body hash');
        $this->assertNotEmpty($receivedMessage->getAttributes(), 'Received message attributes');

        $deleteResult = $connector->deleteMessage($receivedMessage->getReceiptHandle());
        $this->assertInstanceOf(ResultMetadata::class, $deleteResult);
        $this->assertSame(200, $deleteResult->getStatusCode(), 'Delete result status should be 200');
        $this->assertSame($queueUrl, $deleteResult->getEffectiveUri(), 'Delete result effective URI');
        $this->assertNotEmpty($deleteResult->getHeaders(), 'Delete result headers not empty');
        $this->assertNotEmpty($deleteResult->getTransferStats(), 'Delete result transfer stats not empty');
    }
}
