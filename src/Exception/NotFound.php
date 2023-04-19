<?php
declare(strict_types=1);

namespace Caplet\Exception;

use Caplet\Exception;
use Psr\Container\NotFoundExceptionInterface;

class NotFound extends Exception implements NotFoundExceptionInterface
{
}
