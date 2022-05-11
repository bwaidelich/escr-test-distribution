<?php
declare(strict_types=1);
namespace Neos\EventStore\Model;

final class Event
{
    public function __construct(
        public readonly EventId $id,
        public readonly EventType $type,
        public readonly EventData $data,
        public readonly EventMetadata $metadata,
    ) {}
}