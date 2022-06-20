<?php
declare(strict_types=1);

namespace Neos\ContentRepository\CommandHandler;

use Neos\ContentRepository\Command\CommandInterface;
use Neos\ContentRepository\Command\CreateContentStream;
use Neos\ContentRepository\Command\RemoveContentStream;
use Neos\ContentRepository\ContentRepository;
use Neos\ContentRepository\Event\ContentStreamWasAdded;
use Neos\ContentRepository\Event\ContentStreamWasRemoved;
use Neos\ContentRepository\EventStore\Events;
use Neos\ContentRepository\EventStore\EventsToPublish;
use Neos\ContentRepository\Projection\ContentStream\ContentStreamFinder;
use Neos\ContentRepository\Projection\ContentStream\ContentStreamProjection;
use Neos\ContentRepository\ValueObject\ContentStreamId;
use Neos\EventStore\Model\EventStream\ExpectedVersion;
use Neos\EventStore\Model\Event\StreamName;

final class ContentStreamCommandHandler implements CommandHandlerInterface
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

    private function handleCreateContentStream(CreateContentStream $command): EventsToPublish
    {
        return new EventsToPublish($this->streamName($command->contentStreamId), Events::with(new ContentStreamWasAdded($command->contentStreamId)), ExpectedVersion::NO_STREAM());
    }

    private function handleRemoveContentStream(RemoveContentStream $command, ContentStreamFinder $contentStreamFinder): EventsToPublish
    {
        if (!$contentStreamFinder->has($command->contentStreamId)) {
            throw new \InvalidArgumentException(sprintf('Content stream with id "%s" can\'t be removed because it doesn\'t exist', $command->contentStreamId->value), 1652340630);
        }
        return new EventsToPublish($this->streamName($command->contentStreamId), Events::with(new ContentStreamWasRemoved($command->contentStreamId)), ExpectedVersion::STREAM_EXISTS());
    }

    private function streamName(ContentStreamId $contentStreamId): StreamName
    {
        return StreamName::fromString('contentStream:' . $contentStreamId->value);
    }
}
