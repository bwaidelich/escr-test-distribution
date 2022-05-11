<?php
declare(strict_types=1);
namespace Neos\ContentRepository\Projection\ContentStream;

use Neos\ContentRepository\Event\ContentStreamWasAdded;
use Neos\ContentRepository\Event\ContentStreamWasRemoved;
use Neos\ContentRepository\EventStore\EventNormalizer;
use Neos\ContentRepository\Projection\ProjectionInterface;
use Neos\EventStore\CatchUp\CatchUp;
use Neos\EventStore\CatchUp\CheckpointStorageInterface;
use Neos\EventStore\Model\Event;
use Neos\EventStore\Model\EventEnvelope;
use Neos\EventStore\Model\EventStreamInterface;
use Neos\EventStore\Model\SequenceNumber;
use Neos\EventStore\ProvidesSetupInterface;

/**
 * @implements ProjectionInterface<ContentStreamFinder>
 */
final class ContentStreamProjection implements ProjectionInterface
{
    public function __construct(
        private readonly ContentStreamRepositoryInterface $repository,
        private readonly EventNormalizer $eventNormalizer,
        private readonly CheckpointStorageInterface $checkpointStorage,
    ) {}

    public function setUp(): void
    {
        if ($this->repository instanceof ProvidesSetupInterface) {
            $this->repository->setup();
        }
        if ($this->checkpointStorage instanceof ProvidesSetupInterface) {
            $this->checkpointStorage->setup();
        }
    }

    public function reset(): void
    {
        $this->repository->reset();
        $this->checkpointStorage->acquireLock();
        $this->checkpointStorage->updateAndReleaseLock(SequenceNumber::none());
    }

    public function canHandle(Event $event): bool
    {
        return method_exists($this, 'when' . $event->type->value);
    }

    private function whenContentStreamWasAdded(ContentStreamWasAdded $event): void
    {
        $this->repository->add($event->contentStreamId);
    }

    private function whenContentStreamWasRemoved(ContentStreamWasRemoved $event): void
    {
        $this->repository->remove($event->contentStreamId);
    }

    public function getState(): ContentStreamFinder
    {
        return new ContentStreamFinder($this->repository);
    }

    private function apply(EventEnvelope $eventEnvelope): void
    {
        if (!$this->canHandle($eventEnvelope->event)) {
            return;
        }
        $eventInstance = $this->eventNormalizer->denormalize($eventEnvelope->event);
        $this->{'when' . $eventEnvelope->event->type->value}($eventInstance);
    }

    public function catchUp(EventStreamInterface $eventStream): void
    {

        $catchUp = CatchUp::create($this->apply(...), $this->checkpointStorage);
        $catchUp->run($eventStream);
    }

    public function getSequenceNumber(): SequenceNumber
    {
        return $this->checkpointStorage->getHighestAppliedSequenceNumber();
    }
}
