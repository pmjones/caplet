<?php
declare(strict_types=1);

namespace Caplet;

use stdClass;

class FakeCaplet extends Caplet
{
	public function __construct(array $config = [])
	{
		parent::__construct($config);
		$this->factory(
			FakeHelloInterface::class,
			fn (Caplet $caplet) => $caplet->get(FakeHello::class)
		);
	}
}
