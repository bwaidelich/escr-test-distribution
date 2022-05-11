<?php
declare(strict_types=1);
namespace Neos\EventStore\Model;

/**
 * @extends \IteratorAggregate<EventEnvelope>
 */
interface EventStreamInterface extends \IteratorAggregate
{
    public function withMinimumSequenceNumber(SequenceNumber $sequenceNumber): self;
    public function withMaximumSequenceNumber(SequenceNumber $sequenceNumber): self;
    public function limit(int $limit): self;
    public function backwards(): self;

    /**
     * @return \Traversable|EventEnvelope[]
     */
    public function getIterator(): \Traversable;
}