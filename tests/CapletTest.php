<?php
declare(strict_types=1);

namespace Caplet;

use Caplet\Exception;
use Caplet\FakeBroken;
use Caplet\FakeHello;

class CapletTest extends \PHPUnit\Framework\TestCase
{
    public function test() : void
    {
        $caplet = new FakeCaplet();

        $expect = FakeHello::class;
        $actual = $caplet->get(FakeHello::class);
        $this->assertInstanceOf($expect, $actual);
        $this->assertSame("Hello World", $actual("World"));

        $again = $caplet->get(FakeHello::class);
        $this->assertSame($actual, $again);

        $this->assertFalse($caplet->has(NoSuchClass::class)); // @phpstan-ignore-line

        $this->expectException(Exception\NotFound::class);
        $caplet->get(NoSuchclass::class); // @phpstan-ignore-line
    }

    public function testConfig() : void
    {
        $caplet = new FakeCaplet([
            FakeHello::class => [
                'suffix' => ' !!!',
            ]
        ]);

        $actual = $caplet->get(FakeHello::class);
        $this->assertSame("Hello World !!!", $actual("World"));
    }

    public function testFactory() : void
    {
        $caplet = new FakeCaplet();
        $actual = $caplet->get(FakeHelloInterface::class);
        $this->assertInstanceOf(FakeHello::class, $actual);
    }

    public function testCannotInstantiate() : void
    {
        $caplet = new FakeCaplet();
        try {
            $caplet->get(FakeBroken::class);
            $this->assertFalse(true);
        } catch (Exception\NotInstantiated $e) {
            $this->assertSame(
                "Could not instantiate Caplet\FakeBroken",
                $e->getMessage()
            );

            $p = $e->getPrevious();
            $this->assertInstanceOf(Exception\NotResolved::class, $p);
            $this->assertSame(
                "Cannot create argument for 'Caplet\FakeBroken::\$object' of type 'SplFileObject|stdClass'.",
                $p->getMessage()
            );
        }
    }
}
