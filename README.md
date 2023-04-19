# Caplet

_Caplet_ is a minimal autowiring dependency injection container to handle
basic constructor injection and object factories.

## Getting Started

Instantiate _Caplet_ like so:

```php
use Caplet\Caplet;

$caplet = new Caplet();
```

You can then call one of these PSR-11 methods:

- `get(string $class) : object` to get a shared object instance of `$class`.

- `has(string $class) : bool` to see if an instance is available. (This means
  either a class definition exists; or, in the case of an interface, the
  interface definition exists *and* has a `factory()` entry -- see below for
  the `factory()` method.)

_Caplet_ offers this non-PSR-11 method:

- `new(string $class) : object` to get a new object instance of `$class`.
  (This method is *not* part of PSR-11.)

## Configuration

Configure constructor arguments by passing an array with the structure
`$config['ClassName']['parameterName']` at _Caplet_ construction time. For
example, given the following class ...

```php
namespace Foo;

class Bar
{
    public function __construct(
        protected string $bar,
        protected string $baz
    ) {
    }
}
```

... you would configure the arguments for its parameters like so:

```php
use Caplet\Caplet;
use Foo\Bar;

$caplet = new Caplet([
    Bar::class => [
        'bar' => 'bar-value',
        'baz' => 'baz-value',
    ];
]);

$bar = $caplet->get(Bar::class);
```

Alternatively, extend _Caplet_ and override `__construct()` to accept your own
environment or configuration values, then call the `parent::__construct()` with
the `$config['ClassName']['parameterName']` structure.

```php
namespace Project;

use Caplet\Caplet;

class ProjectCaplet extends Caplet
{
    public function __construct(
        string $bar,
        string $baz,
    ) {
        parent::__construct([
            Foo::class => [
                'bar' => 'bar-value',
                'baz' => 'baz-value',
            ],
        ]);
    }
}
```

## Factories

Extending _Caplet_ also allows you to call the protected `factory()` method
inside the constructor to define the object-creation logic for a given type.
This allows you to specify concrete classes for instantiation in place of
abstracts or interfaces. For example:

```php
namespace Project;

use Caplet\Caplet;
use Project\Log\Logger;
use Psr\Log\LoggerInterface;

class ProjectCaplet extends Caplet
{
    public function __construct(
        string $bar,
        string $baz,
    ) {
        parent::__construct([
            Foo::class => [
                'bar' => 'bar-value',
                'baz' => 'baz-value',
            ],
        ]);

        $this->factory(
            LoggerInterface::class,
            fn (Caplet $caplet) => $caplet->get(Logger::class)
        );
    }
}
```

As seen above, the callable factory logic must have the signature
`function (Caplet $caplet)`, and may specify a return type.

## Parameter Resolution

_Caplet_ will attempt to resolve constructor parameters in this order:

- First, use an argument from $config, if one is available.
- Next, try to `get()` an object of the parameter type .
- Last, use the default parameter value, if one is defined.

If none of these work, _Caplet_ will throw _Exception\NotResolved_.
