<?php

namespace Tavurn\Pipeline;

use Closure;
use Illuminate\Contracts\Pipeline\Pipeline as PipelineContract;
use Tavurn\Foundation\Application;

/**
 * @template Subject
 * @template Return
 */
class Pipeline implements PipelineContract
{
    /**
     * The application instance.
     */
    protected Application $app;

    /**
     * The subject which will be passed to each pipe.
     *
     * @var Subject
     */
    protected $subject;

    /**
     * The pipes through which the subject is passed.
     *
     * @var array<int, class-string>
     */
    protected array $pipes = [];

    /**
     * The method to use on the pipes.
     */
    protected string $using;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public static function new(Application $app): static
    {
        return new static($app);
    }

    /**
     * Set the subject of this pipeline.
     *
     * @param Subject $traveler
     * @return static
     */
    public function send($traveler)
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
    protected function getNext(): Closure
    {
        $pipe = array_shift($this->pipes) ?? false;

        return function ($subject) use ($pipe) {
            if (! $pipe) {
                return $subject;
            }

            if (is_string($pipe)) {
                $pipe = $this->make($pipe)->{$this->using}(...);
            }

            $with = empty($this->pipes) ? [$subject] : [$subject, $this->getNext()];

            return $pipe(...$with);
        };
    }

    protected function make($pipe): mixed
    {
        return $this->app->build($pipe);
    }
}