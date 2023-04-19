<?php
declare(strict_types=1);

namespace Caplet;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use Throwable;

class Caplet implements ContainerInterface
{
    /**
     * @var array<class-string, callable>
     */
    protected array $factories = [];

    /**
     * @var array<class-string, object>
     */
    protected array $instances = [];

    /**
     * @param array<class-string, array<string, mixed>> $config
     */
    public function __construct(protected array $config = [])
    {
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T of object
     */
    public function get(string $class) : mixed
    {
        if (! isset($this->instances[$class])) {
            $this->instances[$class] = $this->new($class);
        }

        /** @var T of object */
        return $this->instances[$class];
    }

    public function has(string $class) : bool
    {
        return class_exists($class)
            || (interface_exists($class) && isset($this->factories[$class]));
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T of object
     */
    public function new(string $class) : object
    {
        if (! $this->has($class)) {
            throw new Exception\NotFound(
                "{$class} not found, or has no factory."
            );
        }

        if (isset($this->factories[$class])) {
            return ($this->factories[$class])($this);
        }

        try {
            return $this->instantiate($class);
        } catch (Throwable $e) {
            throw new Exception\NotInstantiated(
                "Could not instantiate {$class}",
                previous: $e
            );
        }
    }

    /**
     * @param class-string $class
     */
    protected function factory(string $class, callable $factory) : void
    {
        $this->factories[$class] = $factory;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T of object
     */
    protected function instantiate(string $class) : object
    {
        $constructor = (new ReflectionClass($class))->getConstructor();

        $arguments = $constructor
            ? $this->arguments($class, $constructor)
            : [];

        /** @var T of object */
        return new $class(...$arguments);
    }

    /**
     * @param class-string $declaringClass
     * @return mixed[]
     */
    protected function arguments(
        string $declaringClass,
        ReflectionMethod $constructor,
    ) : array
    {
        $arguments = [];
        $parameters = $constructor->getParameters();

        foreach ($parameters as $parameter) {
            $arguments[] = $this->argument($declaringClass, $parameter);
        }

        return $arguments;
    }

    /**
     * @param class-string $declaringClass
     */
    protected function argument(
        string $declaringClass,
        ReflectionParameter $parameter,
    ) : mixed
    {
        $name = $parameter->getName();

        // is there a config element for this class and parameter?
        if (isset($this->config[$declaringClass][$name])) {
            return $this->config[$declaringClass][$name];
        }

        $type = $parameter->getType();

        if (! $type instanceof ReflectionNamedType) {
            // not a named type, try for the default value
            return $this->default($declaringClass, $parameter, $name, $type);
        }

        /** @var class-string */
        $parameterClass = $type->getName();

        // is the parameter type an existing class?
        if ($this->has($parameterClass)) {
            // use an object of that type
            return $this->get($parameterClass);
        }

        // no configured value, not an existing class;
        // try for the default value
        return $this->default($declaringClass, $parameter, $name, $type);
    }

    /**
     * @param class-string $declaringClass
     */
    protected function default(
        string $declaringClass,
        ReflectionParameter $parameter,
        string $name,
        ?ReflectionType $type,
    ) : mixed
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new Exception\NotResolved(
            "Cannot create argument for '{$declaringClass}::\${$name}' of type '{$type}'."
        );
    }
}
