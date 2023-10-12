<?php

namespace Tavurn\Pipeline;

use Closure;
use Illuminate\Contracts\Pipeline\Pipeline as PipelineContract;

/**
 * @template Subject
 * @template Return
 */
class Pipeline implements PipelineContract
{
    /**
     * The subject which will be passed to each pipe.
     *
     * @var Subject
     */
    protected $subject;

    /**
     * The pipes through which the subject is passed.
     *
     * @var array<int, object>
     */
    protected array $pipes = [];

    /**
     * The method to use on the pipes.
     */
    protected string $using;

    public static function new(): static
    {
        return new static();
    }

    /**
     * Set the subject of this pipeline.
     *
     * @param Subject $traveler
     * @return static
     */
    public function send($traveler): static
    {
        $this->subject = $traveler;

        return $this;
    }

    /**
     * Declare the pipes through which the subject is passed.
     *
     * @param array $stops
     * @return static
     */
    public function through($stops): static
    {
        $this->pipes = $stops;

        return $this;
    }

    /**
     * Declare the method that is called on the pipes.
     *
     * @param $method
     * @return static
     */
    public function via($method): static
    {
        $this->using = $method;

        return $this;
    }

    /**
     * Execute all the pipes and finally execute the passed closure.
     *
     * @param (Closure(Subject): Return) $destination
     * @return Return
     */
    public function then(Closure $destination): mixed
    {
        $this->pipes[] = $destination;

        $fuse = $this->getNext();

        return $fuse($this->subject);
    }

    /**
     * @return Return
     */
    public function thenReturn(): mixed
    {
        return $this->then(function ($subject) {
            return $subject;
        });
    }

    /**
     * Get the next item in the pipeline stack.
     */
    protected function getNext(int $cur = 0): Closure
    {
        $pipe = $this->pipes[$cur] ?? false;

        return function ($subject) use ($pipe, $cur) {
            if (! $pipe) {
                return $subject;
            }

            $with = empty($this->pipes) ? [$subject] : [$subject, $this->getNext(++$cur)];

            if (! is_callable($pipe)) {
                return $pipe->{$this->using}(...$with);
            }

            return $pipe(...$with);
        };
    }
}