<?php
declare(strict_types=1);

namespace Neos\ContentRepository\CommandHandler;

use Neos\ContentRepository\Command\CommandInterface;
use Neos\ContentRepository\Command\CreateContentStream;
use Neos\ContentRepository\Event\ContentStreamWasAdded;
use Neos\ContentRepository\Event\NodeWasCreated;
use Neos\ContentRepository\EventStore\Events;
use Neos\ContentRepository\EventStore\EventsToPublish;
use Neos\ContentRepository\ValueObject\ContentStreamId;
use Neos\ContentRepository\ValueObject\NodeId;
use Neos\EventStore\Model\ExpectedVersion;
use Neos\EventStore\Model\StreamName;

final class ContentStreamCommandHandler implements CommandHandlerInterface
{

    public function canHandle(CommandInterface $command): bool
    {
        return in_array($command::class, [CreateContentStream::class], true);
    }

    public function handle(CommandInterface $command): EventsToPublish
    {
        return match ($command::class) {
            CreateContentStream::class => new EventsToPublish($this->streamName($command->contentStreamId), Events::with(new ContentStreamWasAdded($command->contentStreamId)), ExpectedVersion::NO_STREAM()),
        };
    }

    private function streamName(ContentStreamId $contentStreamId): StreamName
    {
        return StreamName::fromString('contentStream:' . $contentStreamId->value);
    }
}
