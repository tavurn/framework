<?php

namespace Tavurn\Database;

use Tavurn\Database\Concerns\Anemic;
use Tavurn\Database\Concerns\Creatable;
use Tavurn\Database\Concerns\HasEntityManager;

/**
 * @mixin QueryBuilder<static>
 */
abstract class Model
{
    use Anemic, Creatable, HasEntityManager;

    public static function find(int $id, $lockMode = null, $lockVersion = null): ?static
    {
        return static::getRepository()->find(
            $id, $lockMode, $lockVersion,
        );
    }

    /**
     * @return QueryBuilder<static>
     */
    public static function query(string $alias = null): QueryBuilder
    {
        $alias ??= strtolower(
            class_basename(static::class),
        );

        return static::getRepository()
            ->createQueryBuilder($alias);
    }

    /**
     * @return array<int, static>
     */
    public static function all(): array
    {
        return static::getRepository()->findAll();
    }

    /**
     * @return QueryBuilder<static>|mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return static::query()->{$name}(...$arguments);
    }
}
