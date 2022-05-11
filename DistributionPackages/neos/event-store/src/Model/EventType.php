<?php
declare(strict_types=1);
namespace Neos\EventStore\Model;

/// TODO make flightweight
final class EventType
{
    private function __construct(
        public readonly string $value,
    ) {}

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
