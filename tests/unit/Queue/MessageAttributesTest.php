<?php
declare(strict_types=1);

namespace Ndthuan\Tests\AwsSqsWrapper\Queue;

use Ndthuan\AwsSqsWrapper\Queue\MessageAttributes;
use PHPUnit\Framework\TestCase;

class MessageAttributesTest extends TestCase
{
    /**
     * @var MessageAttributes
     */
    private $messageAttributes;

    protected function setUp()
    {
        parent::setUp();

        $this->messageAttributes = new MessageAttributes();
    }

    public function testAddNumber()
    {
        $this->messageAttributes->addNumber('Counter', '123');

        $this->assertSame([
            'Counter' => [
                'DataType'    => 'Number',
                'StringValue' => '123',
            ],
        ], $this->messageAttributes->toArray());
    }

    public function testAddString()
    {
        $this->messageAttributes->addString('SomeKey', 'lorem-ipsum');

        $this->assertSame(
            [
                'SomeKey' => [
                    'DataType'    => 'String',
                    'StringValue' => 'lorem-ipsum',
                ],
            ],
            $this->messageAttributes->toArray()
        );
    }

    public function testRemove()
    {
        $this->messageAttributes->addNumber('Counter', '123');

        $this->assertSame(
            [
                'Counter' => [
                    'DataType'    => 'Number',
                    'StringValue' => '123',
                ],
            ],
            $this->messageAttributes->toArray(),
            'Original attributes before removing is correct'
        );

        $this->messageAttributes->remove('Counter');
        $this->assertEmpty($this->messageAttributes->toArray());
    }
}
