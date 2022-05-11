<?php
declare(strict_types=1);
namespace Neos\ContentRepository;

use Neos\ContentRepository\Command\CommandInterface;
use Neos\ContentRepository\CommandHandler\CommandHandlerInterface;
use Neos\ContentRepository\EventStore\EventsToPublish;

final class CommandBus
{
    /**
     * @var CommandHandlerInterface[]
     */
    private array $handlers;

    public function __construct(CommandHandlerInterface ...$handlers)
    {
        $this->handlers = $handlers;
    }

    public function handle(CommandInterface $command): EventsToPublish
    {
        // TODO fail if multiple handlers can handle the same command
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($command)) {
                return $handler->handle($command);
            }
        }
        throw new \RuntimeException(sprintf('No handler found for Command "%s"', get_debug_type($command)), 1649582778);
    }

}