<?php
declare(strict_types=1);

namespace Neos\ContentRepository\EventStore;


use Neos\EventStore\Model\ExpectedVersion;
use Neos\EventStore\Model\StreamName;

final class EventsToPublish
{

    public function __construct(
        public readonly StreamName $streamName,
        public readonly Events $events,
        public readonly ExpectedVersion $expectedVersion,
    ) {}
}