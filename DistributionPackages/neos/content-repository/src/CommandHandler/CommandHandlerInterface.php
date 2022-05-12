<?php
declare(strict_types=1);

namespace Neos\ContentRepository\CommandHandler;

use Neos\ContentRepository\Command\CommandInterface;
use Neos\ContentRepository\ContentRepository;
use Neos\ContentRepository\EventStore\EventsToPublish;

interface CommandHandlerInterface
{
    public function canHandle(CommandInterface $command): bool;
    public function handle(CommandInterface $command, ContentRepository $contentRepository): EventsToPublish;
}
