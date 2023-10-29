<?php

namespace Tavurn\Database\Concerns;

trait Creatable
{
    use Anemic;

    public static function create(array $properties = [], bool $flush = true): static
    {
        $entity = new static();

        $entity->set($properties, $flush);

        return $entity;
    }
}