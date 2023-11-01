<?php

namespace Tavurn\Database;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder as OQB;
use Illuminate\Support\Str;

/**
 * @template T
 *
 * @mixin OQB
 */
readonly class QueryBuilder
{
    protected OQB $builder;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->builder = new OQB($manager);
    }

    /**
     * @return T[]
     */
    public function get(): array
    {
        return $this->getQuery()->getArrayResult();
    }

    /**
     * @return T|null
     */
    public function first()
    {
        return $this->limit(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @template Default
     *
     * @param Default $default
     * @return T|Default
     */
    public function firstOr($default)
    {
        return $this->first() ?? $default;
    }

    public function where($column, $operator = null, $value = null): static
    {
        $column = $this->parseColumnName($column);

        if (($argc = func_num_args()) === 1) {
            $this->builder->where(...func_get_args());

            return $this;
        }

        if ($value === null && $argc !== 3) {
            $value = $operator;
            $operator = '=';
        }

        $id = $this->randomParamId();

        $this->builder->where("{$column} {$operator} :{$id}")
            ->setParameter($id, $value);

        return $this;
    }

    /**
     * @param int|null $count
     */
    public function limit($count): static
    {
        $this->builder->setMaxResults($count);

        return $this;
    }

    /**
     * @param int|null $count
     */
    public function take($count): static
    {
        return $this->limit($count);
    }

    protected function parseColumnName(string $column): string
    {
        [$column, $residual] = explode(' ', $column, 2);

        if (! str_contains($column, '.')) {
            $column = $this->builder->getRootAliases()[0] . '.' . $column;
        }

        return $column . ' ' . $residual;
    }

    protected function randomParamId(): string
    {
        return 'p' . Str::random();
    }

    /**
     * @return static|OQB|mixed
     */
    protected function proxyCallToInnerBuilder(string $method, array $parameters)
    {
        $method = ltrim($method, '_');

        $result = $this->builder->{$method}(...$parameters);

        if ($result !== $this->builder) {
            return $result;
        }

        return $this;
    }

    /**
     * @return static|OQB|mixed
     */
    public function __call(string $name, array $arguments)
    {
        return $this->proxyCallToInnerBuilder($name, $arguments);
    }
}
