<?php
declare(strict_types=1);

namespace Neos\ContentRepository\CommandHandler;

use Neos\ContentRepository\Command\CommandInterface;
use Neos\ContentRepository\ContentRepository;
use Neos\ContentRepository\EventStore\Events;
use Neos\ContentRepository\EventStore\EventsToPublish;
use Neos\ContentRepository\Command\CreateNode;
use Neos\ContentRepository\Event\NodeWasCreated;
use Neos\ContentRepository\Projection\ContentStream\ContentStreamFinder;
use Neos\ContentRepository\Projection\ContentStream\ContentStreamProjection;
use Neos\ContentRepository\ValueObject\ContentStreamId;
use Neos\EventStore\Model\ExpectedVersion;
use Neos\EventStore\Model\StreamName;

final class NodeCommandHandler implements CommandHandlerInterface
{

    public function canHandle(CommandInterface $command): bool
    {
        return method_exists($this, 'handle' . (new \ReflectionClass($command))->getShortName());
    }

    public function handle(CommandInterface $command, ContentRepository $contentRepository): EventsToPublish
    {
        /** @var ContentStreamFinder $contentStreamFinder */
        $contentStreamFinder = $contentRepository->projectionState(ContentStreamProjection::class);
        return $this->{'handle' . (new \ReflectionClass($command))->getShortName()}($command, $contentStreamFinder);
    }

    private function handleCreateNode(CreateNode $command, ContentStreamFinder $contentStreamFinder): EventsToPublish
    {
        if (!$contentStreamFinder->has($command->contentStreamId)) {
            throw new \InvalidArgumentException(sprintf('Failed to add node with id "%s" in content stream "%s" because the content stream does not exist', $command->nodeId->value, $command->contentStreamId->value), 1652340930);
        }
        return new EventsToPublish($this->streamName($command->contentStreamId), Events::with(new NodeWasCreated($command->contentStreamId, $command->nodeId)), ExpectedVersion::STREAM_EXISTS());
    }

    private function streamName(ContentStreamId $contentStreamId): StreamName
    {
        return StreamName::fromString('contentStream:' . $contentStreamId->value);
    }
}
