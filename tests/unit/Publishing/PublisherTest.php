<?php
declare(strict_types=1);

namespace Ndthuan\AwsSqsWrapper\Publishing;

use Ndthuan\AwsSqsWrapper\Queue\Connector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PublisherTest extends TestCase
{
    /**
     * @var Connector|MockObject
     */
    private $connectorMock;

    /**
     * @var Publisher
     */
    private $publisher;

    protected function setUp()
    {
        parent::setUp();

        $this->connectorMock = $this->getMockBuilder(Connector::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->publisher = new Publisher($this->connectorMock);
    }

    public function testPublishString()
    {
        $testMessageBody = 'lorem ipsum';

        $this->connectorMock->expects($this->once())
                            ->method('sendMessage')
                            ->with('lorem ipsum');

        $this->publisher->publishString($testMessageBody);
    }

    public function testPublishJsonString()
    {
        $data = [
            'lorem' => 'ipsum',
        ];

        $this->connectorMock->expects($this->once())
                            ->method('sendMessage')
                            ->with('{"lorem":"ipsum"}');

        $this->publisher->publishJsonSerializable($data);
    }
}
