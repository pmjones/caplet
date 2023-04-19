<?php
declare(strict_types=1);

namespace Caplet;

use SplFileObject;
use stdClass;

class FakeBroken
{
    public function __construct(
        protected SplFileObject|stdClass $object
    ) {
    }
}
