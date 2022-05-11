<?php
declare(strict_types=1);
namespace Neos\ContentRepositoryRegistry\Factories;

use Neos\ContentRepository\CommandBus;
use Neos\ContentRepository\CommandHandler\ContentStreamCommandHandler;
use Neos\ContentRepository\CommandHandler\NodeCommandHandler;
use Neos\ContentRepository\ContentRepository;
use Neos\ContentRepository\EventStore\EventNormalizer;
use Neos\ContentRepository\EventStore\EventStoreFactoryInterface;
use Neos\ContentRepository\Projection\ProjectionCatchUpTriggerFactoryInterface;
use Neos\ContentRepository\Projection\ProjectionFactoryInterface;
use Neos\ContentRepository\Projection\Projections;
use Neos\ContentRepository\ValueObject\ContentRepositoryId;
use Neos\ContentRepositoryRegistry\Exception\InvalidConfigurationException;
use Neos\EventStore\EventStoreInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;

#[Flow\Scope("singleton")]
class ContentRepositoryFactory
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly EventNormalizer $eventNormalizer,
    ) {}

    /**
     * @param ContentRepositoryId $id
     * @param array<mixed> $settings
     * @return ContentRepository
     */
    public function create(ContentRepositoryId $id, array $settings): ContentRepository
    {
        assert(isset($settings['eventStore']) && is_array($settings['eventStore']), InvalidConfigurationException::fromMessage('Missing/invalid "eventStore" setting for content repository %s', $id->value));
        assert(isset($settings['eventStore']['factoryObjectName']) && is_string($settings['eventStore']['factoryObjectName']), InvalidConfigurationException::fromMessage('Missing/invalid "eventStore.factoryObjectName" setting for content repository %s', $id->value));
        assert(!isset($settings['eventStore']['factoryMethodName']) || is_string($settings['eventStore']['factoryMethodName']), InvalidConfigurationException::fromMessage('Invalid "eventStore.factoryMethodName" setting for content repository %s. Expected string got: %s', $id->value, get_debug_type($settings['eventStore']['factoryMethodName'] ?? null)));
        assert(!isset($settings['eventStore']['options']) || is_array($settings['eventStore']['options']), InvalidConfigurationException::fromMessage('Invalid "eventStore.options" setting for content repository %s. Expected array got: %s', $id->value, get_debug_type($settings['eventStore']['options'] ?? null)));

        $eventStoreFactory = $this->objectManager->get($settings['eventStore']['factoryObjectName']);
        if (!$eventStoreFactory instanceof EventStoreFactoryInterface) {
            throw new \RuntimeException(sprintf('Invalid "eventStore.factoryObjectName" specified. Expected instance of %s, got: %s', EventStoreFactoryInterface::class, get_debug_type($eventStoreFactory)), 1652283202);
        }
        $eventStore = $eventStoreFactory->create($id, $settings['eventStore']['options'] ?? []);

        assert(isset($settings['projectionCatchUpTrigger']) && is_array($settings['projectionCatchUpTrigger']), InvalidConfigurationException::fromMessage('Missing/invalid "projectionCatchUpTrigger" setting for content repository %s', $id->value));
        assert(isset($settings['projectionCatchUpTrigger']['factoryObjectName']) && is_string($settings['projectionCatchUpTrigger']['factoryObjectName']), InvalidConfigurationException::fromMessage('Missing/invalid "projectionCatchUpTrigger.factoryObjectName" setting for content repository %s', $id->value));
        $projectionCatchUpTriggerFactory = $this->objectManager->get($settings['projectionCatchUpTrigger']['factoryObjectName']);
        if (!$projectionCatchUpTriggerFactory instanceof ProjectionCatchUpTriggerFactoryInterface) {
            throw new \RuntimeException(sprintf('Invalid "projectionCatchUpTrigger.factoryObjectName" specified. Expected instance of %s, got: %s', ProjectionCatchUpTriggerFactoryInterface::class, get_debug_type($projectionCatchUpTriggerFactory)), 1652199504);
        }
        $projectionCatchUpTrigger = $projectionCatchUpTriggerFactory->create($id, $settings['projectionCatchUpTrigger']['options'] ?? []);

        $projections = Projections::create();
        assert(isset($settings['projections']) && is_array($settings['projections']));
        foreach ($settings['projections'] as $projectionName => $projectionOptions) {
            $projectionFactory = $this->objectManager->get($projectionOptions['factoryObjectName']);
            if (!$projectionFactory instanceof ProjectionFactoryInterface) {
                throw InvalidConfigurationException::fromMessage('Projection factory object name for projection "%s" (content repository "%s") is not an instance of %s but %s in content repository "%s"', $projectionName, $id->value, ProjectionFactoryInterface::class, get_debug_type($projectionFactory));
            }
            $projection = $projectionFactory->create($id, $projectionOptions['options'] ?? []);
            $projections = $projections->with($projection);
        }

        return new ContentRepository(
            new CommandBus(
                new NodeCommandHandler(),
                new ContentStreamCommandHandler(),
            ),
            $eventStore,
            $projections,
            $this->eventNormalizer,
            $projectionCatchUpTrigger,
        );
    }
}
