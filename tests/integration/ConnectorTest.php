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
        $connector = new Connector($this->sqsClient, 'http://localstack:4576/queue/test-queue');

        $sendResult = $connector->sendMessage('Xin chao');
        $this->assertNotEmpty($sendResult->getMessageId(), 'Sent message id must not be empty');

        $receiveResult = $connector->receiveMessage();
        $this->assertCount(1, $receiveResult->getMessages(), 'Received message count must be one');

        $receivedMessage = $receiveResult->getMessages()[0];
        $this->assertSame(
            $sendResult->getMessageId(),
            $receivedMessage->getId(),
            'Received message id must be the same as sent message id'
        );

        $deleteResult = $connector->deleteMessage($receivedMessage->getReceiptHandle());
        $this->assertInstanceOf(ResultMetadata::class, $deleteResult);
    }
}
