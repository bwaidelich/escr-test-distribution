<?php
declare(strict_types=1);
namespace Neos\ContentRepository\Projection;

use Neos\ContentRepository\ContentRepository;

/**
 * Interface for a class that (asynchronously) triggers a catchup of affected projections after a {@see ContentRepository::handle()} call
 */
interface ProjectionCatchUpTriggerInterface
{
    public function triggerCatchUp(Projections $projections): void;
}
