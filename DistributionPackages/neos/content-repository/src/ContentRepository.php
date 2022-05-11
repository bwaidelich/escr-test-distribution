<?php
declare(strict_types=1);
namespace Neos\ContentRepository;

use Neos\ContentRepository\Command\CommandInterface;
use Neos\ContentRepository\CommandHandler\CommandResult;
use Neos\ContentRepository\CommandHandler\PendingProjections;
use Neos\ContentRepository\EventStore\EventInterface;
use Neos\ContentRepository\EventStore\EventNormalizer;
use Neos\ContentRepository\Exception\ProjectionCatchUpFailed;
use Neos\ContentRepository\Projection\ProjectionCatchUpTriggerInterface;
use Neos\ContentRepository\Projection\ProjectionInterface;
use Neos\ContentRepository\Projection\Projections;
use Neos\ContentRepository\Projection\ProjectionStateInterface;
use Neos\EventStore\EventStoreInterface;
use Neos\EventStore\Model\Event;
use Neos\EventStore\Model\EventId;
use Neos\EventStore\Model\EventMetadata;
use Neos\EventStore\Model\Events;
use Neos\EventStore\Model\SetupResult;
use Neos\EventStore\Model\VirtualStreamName;
use Neos\EventStore\ProvidesSetupInterface;

final class ContentRepository
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly EventStoreInterface $eventStore,
        private readonly Projections $projections,
        private readonly EventNormalizer $eventNormalizer,
        private readonly ProjectionCatchUpTriggerInterface $projectionCatchUpTrigger,
    )
    {}

    public function handle(CommandInterface $command): CommandResult
    {
        $eventsToPublish = $this->commandBus->handle($command);
        $normalizedEvents = Events::fromArray($eventsToPublish->events->map(fn (EventInterface $event) => $this->normalizeEvent($event)));
        $commitResult = $this->eventStore->commit($eventsToPublish->streamName, $normalizedEvents, $eventsToPublish->expectedVersion);
        $pendingProjections = PendingProjections::fromProjectionsAndEventsAndSequenceNumber($this->projections, $normalizedEvents, $commitResult->sequenceNumber);
        $this->projectionCatchUpTrigger->triggerCatchUp($pendingProjections->projections);

        return new CommandResult($pendingProjections, $commitResult);
    }

    private function normalizeEvent(EventInterface $event): Event
    {
        // TODO support decorated events (specifying id, metadata, ...)
        return new Event(
            EventId::create(),
            $this->eventNormalizer->getEventType($event),
            $this->eventNormalizer->getEventData($event),
            EventMetadata::none(),
        );
    }

    /**
     * @template T of ProjectionState
     * @param class-string<ProjectionInterface<T>> $projectionClassName
     * @return T
     */
    public function projectionState(string $projectionClassName): ProjectionStateInterface
    {
        return $this->projections->get($projectionClassName)->getState();
    }

    /**
     * @template T of ProjectionState
     * @param class-string<ProjectionInterface<T>> $projectionClassName
     * @throws ProjectionCatchUpFailed
     */
    public function catchUpProjection(string $projectionClassName): void
    {
        $projection = $this->projections->get($projectionClassName);
        // TODO allow custom stream name per projection
        $streamName = VirtualStreamName::all();
        $eventStream = $this->eventStore->load($streamName);
        $projection->catchUp($eventStream);
    }

    public function setUp(): SetupResult
    {
        if ($this->eventStore instanceof ProvidesSetupInterface) {
            $result = $this->eventStore->setup();
            // TODO better result object
            if ($result->errors !== []) {
                return $result;
            }
        }
        foreach ($this->projections as $projection) {
            $projection->setUp();
        }
        return SetupResult::success('done');
    }

    public function resetProjectionStates(): void
    {
        foreach ($this->projections as $projection) {
            $projection->reset();
        }
    }

    /** TODO  public function getNodeTypeManager() */
    /** TODO  public function getContentGraph() */
}
