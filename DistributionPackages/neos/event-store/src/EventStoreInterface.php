<?php
declare(strict_types=1);
namespace Neos\EventStore;

use Neos\EventStore\Model\CommitResult;
use Neos\EventStore\Model\EventStreamInterface;
use Neos\EventStore\Model\ExpectedVersion;
use Neos\EventStore\Model\StreamName;
use Neos\EventStore\Model\VirtualStreamName;
use Neos\EventStore\Model\Events;

interface EventStoreInterface
{
    public function load(StreamName|VirtualStreamName $streamName): EventStreamInterface;
    public function commit(StreamName $streamName, Events $events, ExpectedVersion $expectedVersion): CommitResult;
    public function deleteStream(StreamName $streamName): void;
}