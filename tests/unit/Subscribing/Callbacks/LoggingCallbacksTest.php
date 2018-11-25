<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Subscribing\Callbacks;

use Exception;
use Ndthuan\AwsSqsWrapper\Queue\ReceivedMessage;
use Ndthuan\AwsSqsWrapper\Subscribing\Exception\FatalException;
use Ndthuan\AwsSqsWrapper\Subscribing\Exception\LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LoggingCallbacksTest extends TestCase
{
    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var LoggingCallbacks
     */
    private $loggingCallbacks;

    protected function setUp()
    {
        parent::setUp();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $this->loggingCallbacks = new LoggingCallbacks($this->loggerMock);
    }

    public function testOnMessageProcessed()
    {
        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('Successfully processed SQS message', ['messageId' => 'example-id']);

        $this->loggingCallbacks->onMessageProcessed($this->createMessage());
    }

    public function testOnUncaughtException()
    {
        $thrownException = new Exception();

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with(
                'Uncaught exception when processing SQS message',
                ['messageId' => 'example-id', 'exception' => $thrownException]
            );

        $this->loggingCallbacks->onUncaughtException($this->createMessage(), $thrownException);
    }

    public function testOnMessageReceived()
    {
        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('Received SQS message', ['messageId' => 'example-id']);

        $this->loggingCallbacks->onMessageReceived($this->createMessage());
    }

    public function testOnLogicException()
    {
        $thrownException = new LogicException();

        $this->loggerMock->expects($this->once())
            ->method('debug')
            ->with(
                'Deleted SQS message due to logical exception',
                ['messageId' => 'example-id', 'exception' => $thrownException]
            );

        $this->loggingCallbacks->onLogicException($this->createMessage(), $thrownException);
    }

    public function testOnFatalException()
    {
        $thrownException = new FatalException();

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with(
                'Stopped SQS processing due to fatal exception',
                ['messageId' => 'example-id', 'exception' => $thrownException]
            );

        $this->loggingCallbacks->onFatalException($this->createMessage(), $thrownException);
    }

    /**
     * @return ReceivedMessage
     */
    private function createMessage()
    {
        return new ReceivedMessage('example-id', 'example-recept-handle', 'example-body-hash', 'example-body', []);
    }
}
