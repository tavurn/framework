<?php

namespace Tavurn\Database\Concerns;

use Illuminate\Support\Str;

trait Anemic
{
    use HasEntityManager;

    public function set(array $properties, bool $save = true): static
    {
        foreach ($properties as $name => $value) {
            $this->{$name} = $value;
        }

        if ($save) {
            $this->save();
        }

        return $this;
    }

    public function save(): void
    {
        ($manager = static::getManager())
            ->persist($this);

        $manager->flush($this);
    }

    protected function dynamicGet(string $name)
    {
        return $this->{$name};
    }

    protected function dynamicSet(string $name, $value): static
    {
        return $this->set([
            $name => $value,
        ], false);
    }

    public function __call(string $name, array $arguments)
    {
        [$start, $property] = [
            substr($name, 0, 3),
            substr($name, 3),
        ];

        $property = Str::camel($property);

        return match ($start) {
            'get' => $this->dynamicGet($property),
            'set' => $this->dynamicSet($property, $arguments[0]),
            default => $this->throwBadMethodCallException($name),
        };
    }

    private function throwBadMethodCallException(string $name): never
    {
        $method = static::class . '::' . $name;

        throw new \BadMethodCallException("Method [{$method}] does not exist");
    }
}