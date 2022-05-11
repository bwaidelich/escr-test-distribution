<?php
declare(strict_types=1);
namespace Neos\EventStore\Model;

enum VirtualStreamType: string
{
    case ALL = 'all';
    case CATEGORY = 'category';
    case CORRELATION_ID = 'correlation';
}
