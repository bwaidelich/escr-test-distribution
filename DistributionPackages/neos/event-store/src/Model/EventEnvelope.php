<?php
declare(strict_types=1);
namespace Neos\EventStore\Model;

final class EventEnvelope
{
    public function __construct(
        public readonly Event $event,
        public readonly StreamName $streamName,
        public readonly Version $version,
        public readonly SequenceNumber $sequenceNumber,
        public readonly \DateTimeImmutable $recordedAt,
    ) {}
}