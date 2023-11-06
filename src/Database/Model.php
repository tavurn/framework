<?php

namespace Tavurn\Database;

use Doctrine\ORM\Mapping\Id;
use Tavurn\Database\Concerns\Anemic;
use Tavurn\Database\Concerns\Creatable;
use Tavurn\Database\Concerns\HasEntityManager;

abstract class Model
{
    use Anemic, Creatable, HasEntityManager;

    public static function find(int $id, $lockMode = null, $lockVersion = null): ?static
    {
        return static::getRepository()->find(
            $id, $lockMode, $lockVersion,
        );
    }

    public function delete(bool $flush = true): void
    {
        ($manager = static::getManager())->remove($this);

        if ($flush) {
            $manager->flush($this);
        }
    }

    /**
     * @return QueryBuilder<static>
     */
    public static function query(string $alias = null): QueryBuilder
    {
        $alias ??= static::defaultAlias();

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

    public static function defaultAlias(): string
    {
        return strtolower(class_basename(static::class));
    }

    /**
     * @return QueryBuilder<static>|mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return static::query()->{$name}(...$arguments);
    }
}
