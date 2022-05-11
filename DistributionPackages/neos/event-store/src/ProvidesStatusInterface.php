<?php
declare(strict_types=1);
namespace Neos\EventStore;

use Neos\EventStore\Model\Status;

interface ProvidesStatusInterface
{
    public function status(): Status;
}