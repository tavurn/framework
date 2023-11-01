<?php

namespace Tavurn\Support\Facades;

use Tavurn\Support\Facade;

class Entity extends Facade
{
    protected static function getContainerAccessor(): string
    {
        return \Doctrine\ORM\EntityManager::class;
    }
}
