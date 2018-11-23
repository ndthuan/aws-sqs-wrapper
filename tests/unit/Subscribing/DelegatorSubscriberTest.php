<?php
declare(strict_types=1);

namespace Ndthuan\Tests\AwsSqsWrapper\Subscribing;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Ndthuan\AwsSqsWrapper\Queue\Connector;
use Ndthuan\AwsSqsWrapper\Queue\ReceivedMessage;
use Ndthuan\AwsSqsWrapper\Queue\ReceiveMessageResult;
use Ndthuan\AwsSqsWrapper\Queue\ResultMetadata;
use Ndthuan\AwsSqsWrapper\Subscribing\Callbacks\LoggingCallbacks;
use Ndthuan\AwsSqsWrapper\Subscribing\DelegatorSubscriber;
use Ndthuan\AwsSqsWrapper\Subscribing\Exception\FatalException;
use Ndthuan\AwsSqsWrapper\Subscribing\Exception\LogicException;
use Ndthuan\AwsSqsWrapper\Subscribing\MessageProcessorInterface;
use Ndthuan\AwsSqsWrapper\Subscribing\MessageValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

class DelegatorSubscriberTest extends TestCase
{
    /**
     * @var DelegatorSubscriber
     */
    private $subscriberUnderTest;

    /**
     * @var Connector|MockObject
     */
    private $connectorMock;

    /**
     * @var MessageProcessorInterface|MockObject
     */
    private $messageProcessorMock;

    /**
     * @var ReceivedMessage
     */
    private $fakeReceivedMessage;

    /**
     * @var ResultMetadata
     */
    private $fakeReceiveResultMetadata;

    /**
     * @var TestHandler
     */
    private $testLogHandler;

    /**
     * @var array
     */
    private $receiveMessageOptions;

    protected function setUp()
    {
        parent::setUp();

        $this->connectorMock = $this
            ->getMockBuilder(Connector::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageProcessorMock = $this
            ->getMockBuilder([
                MessageValidatorInterface::class,
                MessageProcessorInterface::class,
            ])
            ->getMock();

        $this->receiveMessageOptions = [
            'WaitTimeSeconds' => 10,
        ];

        $this->testLogHandler = new TestHandler();

        $logger = new Logger('TestLogger', [$this->testLogHandler]);

        $this->subscriberUnderTest = new DelegatorSubscriber(
            $this->messageProcessorMock,
            $this->connectorMock,
            new LoggingCallbacks($logger),
            $this->receiveMessageOptions
        );

        $this->fakeReceiveResultMetadata = ResultMetadata::fromArray([]);

        $this->fakeReceivedMessage = $this->createSampleReceivedMessage();

        $sampleMessages = [
            $this->fakeReceivedMessage,
        ];

        $receiveMessageResult = new ReceiveMessageResult($sampleMessages, $this->fakeReceiveResultMetadata);

        $this->connectorMock
            ->expects($this->once())
            ->method('receiveMessage')
            ->with($this->receiveMessageOptions)
            ->willReturn($receiveMessageResult);

        $this->messageProcessorMock
            ->expects($this->once())
            ->method('processMessage')
            ->with($this->fakeReceivedMessage, $this->fakeReceiveResultMetadata);

        $this->messageProcessorMock
            ->expects($this->once())
            ->method('validateMessage')
            ->with($this->fakeReceivedMessage);
    }

    /**
     * @throws Throwable
     */
    public function testProcessMessageHappyCase()
    {
        $this->connectorMock
            ->expects($this->once())
            ->method('deleteMessage')
            ->with('sample-receipt-handle')
            ->willReturn(ResultMetadata::fromArray([]));

        $this->subscriberUnderTest->pullAndProcessMessages();

        $this->assertTrue($this->testLogHandler->hasInfoThatContains('Successfully processed SQS message'));
    }

    /**
     * @throws Throwable
     */
    public function testProcessMessageOnLogicExceptionMessageShouldBeDeletedAndProcessingShouldContinue()
    {
        $this->connectorMock
            ->expects($this->once())
            ->method('deleteMessage')
            ->with('sample-receipt-handle')
            ->willReturn(ResultMetadata::fromArray([]));

        $this->messageProcessorMock
            ->expects($this->once())
            ->method('processMessage')
            ->with($this->fakeReceivedMessage, $this->fakeReceiveResultMetadata)
            ->willThrowException(new LogicException());

        $this->subscriberUnderTest->pullAndProcessMessages();

        $this->assertTrue($this->testLogHandler->hasInfoThatContains('Deleted SQS message due to logical exception'));
    }

    /**
     * @throws Throwable
     */
    public function testProcessMessageOnFatalExceptionMessageShouldNotBeDeletedAndProcessingShouldStop()
    {
        $this->connectorMock
            ->expects($this->never())
            ->method('deleteMessage');

        $this->messageProcessorMock
            ->expects($this->once())
            ->method('processMessage')
            ->with($this->fakeReceivedMessage, $this->fakeReceiveResultMetadata)
            ->willThrowException(new FatalException());

        $this->expectException(FatalException::class);

        $this->subscriberUnderTest->pullAndProcessMessages();

        $this->assertTrue($this->testLogHandler->hasCriticalThatContains('SQS message processing fatal exception'));
    }

    /**
     * @throws Throwable
     */
    public function testProcessMessageOnUncaughtExceptionMessageShouldNotBeDeletedAndProcessingShouldContinue()
    {
        $this->connectorMock
            ->expects($this->never())
            ->method('deleteMessage');

        $this->messageProcessorMock
            ->expects($this->once())
            ->method('processMessage')
            ->with($this->fakeReceivedMessage, $this->fakeReceiveResultMetadata)
            ->willThrowException(new RuntimeException());

        $this->subscriberUnderTest->pullAndProcessMessages();

        $this->assertTrue($this->testLogHandler->hasErrorThatContains(
            'Uncaught exception when processing SQS message'
        ));
    }

    private function createSampleReceivedMessage(): ReceivedMessage
    {
        return new ReceivedMessage(
            'sample-uuid',
            'sample-receipt-handle',
            'sample-body-hash',
            'body-payload',
            []
        );
    }
}
