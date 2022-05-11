<?php
declare(strict_types=1);
namespace Neos\ContentRepository\Exception;

use Neos\EventSourcing\EventStore\EventEnvelope;

final class ProjectionCatchUpFailed extends \RuntimeException
{
    public static function whileHandlingEvent(string $projectionClassName, EventEnvelope $eventEnvelope, \Throwable $exception): self
    {
        return new self(sprintf('Failed to apply event %s ("%s") to projection %s: %s', $eventEnvelope->getRawEvent()->getIdentifier(), $eventEnvelope->getRawEvent()->getType(), $projectionClassName, $exception->getMessage()), 1651046615, $exception);
    }
}
