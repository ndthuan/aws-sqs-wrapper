# aws-sqs-wrapper
PHP library for simplified SQS queue messages processing.

![Build Status](https://travis-ci.org/ndthuan/aws-sqs-wrapper.svg?branch=master)

## Introduction
- This library converts send/receive/delete SQS messages results into objects. This enables you to utilize code autocompletion without memorizing common attribute names.
- It removes your boilerplate code by introducing simplified send/receive/delete methods.
- It decouples publishing and subscribing responsibilities so that your business logic is free from infrastructure awareness.

## Installation

```bash
composer require ndthuan/aws-sqs-wrapper
```

## Usage

### Sending a message
```php
use Aws\Sqs\SqsClient;
use Ndthuan\AwsSqsWrapper\Publishing\Publisher;
use Ndthuan\AwsSqsWrapper\Queue\Connector;

$queueUrl = ''; // get this from your own AWS SQS setup

// creating a publisher
$sqsClient = new SqsClient(...);
$queueConnector = new Connector($sqsClient, $queueUrl);
$publisher = new Publisher($queueConnector);

// send a string message
$publisher->publishString('My message body');
// or send a json serializable object/array
$myMessage = [
    'my' => 'data',
];
$publisher->publishJsonSerializable($myMessage);
```

### Subscribing to a queue
```php
use Aws\Sqs\SqsClient;
use Ndthuan\AwsSqsWrapper\Queue\Connector;
use Ndthuan\AwsSqsWrapper\Queue\ReceivedMessage;
use Ndthuan\AwsSqsWrapper\Queue\ResultMetadata;
use Ndthuan\AwsSqsWrapper\Subscribing\DelegatorSubscriber;

// defining a message processor
class MyMessageProcessor implements MessageProcessorInterface
{
    /**
     * @inheritdoc
     */
    public function processMessage(ReceivedMessage $message, ResultMetadata $resultMetadata)
    {
        // your processing logic
    }
}

$queueUrl = ''; // get this from your own AWS SQS setup

// creating a subscriber
$sqsClient = new SqsClient(...);
$queueConnector = new Connector($sqsClient, $queueUrl);
$subscriber = new DelegatorSubscriber(new MyMessageProcessor(), $queueConnector);

// run the subscriber
$subscriber->pullAndProcessMessages();
```

## Exception handling

A message processor may throw exceptions or errors. Subscriber will react differently depending on exception type:

- On `LogicException` and `ValidationFailureException`: the corresponding message is deleted from queue and the subscriber continues to process other messages.
- On `FatalException`: the corresponding message is not deleted from queue but the subscriber stops processing and bubbles up the exception.
- On other kinds of `\Throwable`: the corresponding message is not deleted from queue and the subscriber continues to process other messages.
