<?php
declare(strict_types=1);

namespace Caplet;

interface FakeHelloInterface
{
    public function __invoke(string $noun) : string;
}
