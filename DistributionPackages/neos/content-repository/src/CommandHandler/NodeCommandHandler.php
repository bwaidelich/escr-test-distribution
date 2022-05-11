<?php
declare(strict_types=1);

namespace Neos\ContentRepository\CommandHandler;

use Neos\ContentRepository\Command\CommandInterface;
use Neos\ContentRepository\EventStore\Events;
use Neos\ContentRepository\EventStore\EventsToPublish;
use Neos\ContentRepository\Command\CreateNode;
use Neos\ContentRepository\Event\NodeWasCreated;
use Neos\ContentRepository\ValueObject\ContentStreamId;
use Neos\EventStore\Model\ExpectedVersion;
use Neos\EventStore\Model\StreamName;

final class NodeCommandHandler implements CommandHandlerInterface
{

    public function canHandle(CommandInterface $command): bool
    {
        return in_array($command::class, [CreateNode::class], true);
    }

    public function handle(CommandInterface $command): EventsToPublish
    {
        return match ($command::class) {
            CreateNode::class => new EventsToPublish($this->streamName($command->contentStreamId), Events::with(new NodeWasCreated($command->contentStreamId, $command->nodeId)), ExpectedVersion::ANY()),
        };
    }

    private function streamName(ContentStreamId $contentStreamId): StreamName
    {
        return StreamName::fromString('contentStream:' . $contentStreamId->value);
    }
}