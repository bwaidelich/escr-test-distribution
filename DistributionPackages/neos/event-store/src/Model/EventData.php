<?php
declare(strict_types=1);
namespace Neos\EventStore\Model;

final class EventData
{
    private function __construct(
        public readonly string $value,
    ) {}

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
