<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Subscribing;

use Ndthuan\AwsSqsWrapper\Queue\Connector;
use Ndthuan\AwsSqsWrapper\Queue\ReceivedMessage;
use Ndthuan\AwsSqsWrapper\Queue\ReceiveMessageResult;
use Ndthuan\AwsSqsWrapper\Queue\ResultMetadata;
use Ndthuan\AwsSqsWrapper\Subscribing\Callbacks\SubscriberCallbacksInterface;
use Ndthuan\AwsSqsWrapper\Subscribing\Exception\FatalException;
use Ndthuan\AwsSqsWrapper\Subscribing\Exception\LogicException;
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
     * @var SubscriberCallbacksInterface|MockObject
     */
    private $callbacksMock;

    /**
     * @var ReceivedMessage
     */
    private $fakeReceivedMessage;

    /**
     * @var ResultMetadata
     */
    private $fakeReceiveResultMetadata;

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
            ->getMockBuilder(
                [
                    MessageValidatorInterface::class,
                    MessageProcessorInterface::class,
                ]
            )
            ->getMock();

        $this->receiveMessageOptions = [
            'WaitTimeSeconds' => 10,
        ];

        $this->callbacksMock = $this
            ->getMockBuilder(SubscriberCallbacksInterface::class)
            ->getMock();

        $this->subscriberUnderTest = new DelegatorSubscriber(
            $this->messageProcessorMock,
            $this->connectorMock,
            $this->receiveMessageOptions,
            $this->callbacksMock
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

        $this->callbacksMock
            ->expects($this->once())
            ->method('onMessageReceived')
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

        $this->callbacksMock
            ->expects($this->once())
            ->method('onMessageProcessed')
            ->with($this->fakeReceivedMessage);

        $this->subscriberUnderTest->pullAndProcessMessages();
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

        $this->callbacksMock
            ->expects($this->once())
            ->method('onLogicException')
            ->with($this->fakeReceivedMessage, $this->isInstanceOf(LogicException::class));

        $this->subscriberUnderTest->pullAndProcessMessages();
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

        $this->callbacksMock
            ->expects($this->once())
            ->method('onFatalException')
            ->with($this->fakeReceivedMessage, $this->isInstanceOf(FatalException::class));

        $this->expectException(FatalException::class);

        $this->subscriberUnderTest->pullAndProcessMessages();
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

        $this->callbacksMock
            ->expects($this->once())
            ->method('onUncaughtException')
            ->with($this->fakeReceivedMessage, $this->isInstanceOf(RuntimeException::class));

        $this->subscriberUnderTest->pullAndProcessMessages();
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
