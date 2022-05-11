<?php
declare(strict_types=1);
namespace Neos\ContentRepository\CommandHandler;

use Neos\ContentRepository\Projection\ProjectionInterface;
use Neos\ContentRepository\Projection\Projections;
use Neos\ContentRepository\Projection\ProjectionStateInterface;
use Neos\EventStore\Model\Events;
use Neos\EventStore\Model\SequenceNumber;

/**
 * @implements \IteratorAggregate<ProjectionInterface>
 */
final class PendingProjections implements \IteratorAggregate
{
    /**
     * @param Projections<ProjectionInterface<ProjectionStateInterface>> $projections
     * @param array<string, int> $sequenceNumberPerProjection
     */
    public function __construct(
        public readonly Projections $projections,
        private readonly array $sequenceNumberPerProjection,
    ) { }

    public static function fromProjectionsAndEventsAndSequenceNumber(Projections $allProjections, Events $events, SequenceNumber $sequenceNumber): self
    {
        $sequenceNumberInteger = $sequenceNumber->value - $events->count() + 1;
        $pendingProjections = Projections::create();
        $sequenceNumberPerProjection = [];
        foreach ($events as $event) {
            foreach ($allProjections as $projection) {
                if ($projection->canHandle($event)) {
                    $sequenceNumberPerProjection[$projection::class] = $sequenceNumberInteger;
                    if (!$pendingProjections->has($projection::class)) {
                        $pendingProjections = $pendingProjections->with($projection);
                    }
                }
            }
            $sequenceNumberInteger ++;
        }
        return new self($pendingProjections, $sequenceNumberPerProjection);
    }

    /**
     * @param ProjectionInterface<ProjectionStateInterface> $projection
     * @return SequenceNumber
     */
    public function getExpectedSequenceNumber(ProjectionInterface $projection): SequenceNumber
    {
        if (!array_key_exists($projection::class, $this->sequenceNumberPerProjection)) {
            throw new \InvalidArgumentException(sprintf('Projection of class "%s" is not pending', $projection::class), 1652252976);
        }
        return SequenceNumber::fromInteger($this->sequenceNumberPerProjection[$projection::class]);
    }


    public function getIterator(): \Traversable
    {
        return $this->projections->getIterator();
    }
}
