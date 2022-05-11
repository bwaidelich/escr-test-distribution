<?php
declare(strict_types=1);
namespace Neos\ContentRepository\Projection;

interface ProjectionCatchUpTriggerInterface
{
    public function triggerCatchUp(Projections $projections): void;
}
