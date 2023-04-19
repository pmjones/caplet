<?php
declare(strict_types=1);

namespace Caplet;

use SplFileObject;
use stdClass;

class FakeHello implements FakeHelloInterface
{
    public function __construct(
        protected string $suffix = '',
        protected ?stdClass $object = null
    ) {
    }

    public function __invoke(string $noun) : string
    {
        return "Hello {$noun}" . $this->suffix;
    }
}
