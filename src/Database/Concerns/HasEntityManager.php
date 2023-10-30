<?php

namespace Tavurn\Database\Concerns;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Tavurn\Database\Repository;

trait HasEntityManager
{
    public static EntityManager $manager;

    public static function getManager(): EntityManager
    {
        return static::$manager ??= app(EntityManager::class);
    }

    /**
     * @return Repository<static>
     */
    public static function getRepository(): EntityRepository
    {
        return static::getManager()->getRepository(static::class);
    }
}