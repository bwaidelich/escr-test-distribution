<?php
declare(strict_types=1);
namespace Neos\ContentRepository\Projection;

use Neos\EventStore\Model\EventStreamInterface;
use Neos\EventStore\Model\SequenceNumber;
use Neos\EventStore\Model\Event;

/**
 * Common interface for a Content Repository projection
 *
 * @template TState of ProjectionStateInterface
 */
interface ProjectionInterface
{
    public function setUp(): void;

    public function canHandle(Event $event): bool;
    public function catchUp(EventStreamInterface $eventStream): void;
    public function getSequenceNumber(): SequenceNumber;

    /**
     * @return TState
     */
    public function getState(): ProjectionStateInterface;
    public function reset(): void;
}
