<?php
declare(strict_types=1);
namespace Neos\ContentRepositoryRegistry\Factories;

use Neos\ContentRepository\EventStore\EventNormalizer;
use Neos\Flow\Annotations as Flow;

#[Flow\Scope("singleton")]
class EventNormalizerFactory
{
    private ?EventNormalizer $instance = null;

    public function create(): EventNormalizer
    {
        if ($this->instance === null) {
            $this->instance = new EventNormalizer();
        }
        return $this->instance;
    }
}
