<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Tests;

use BeastBytes\View\Latte\Extension\LatteExtension;
use BeastBytes\View\Latte\Tests\Support\BeginBody;
use BeastBytes\View\Latte\Tests\Support\EndBody;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class ExtensionTest extends TestCase
{
    private string|SimpleContainer $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = $this->getContainer();
    }

    #[Test]
    public function getFunctions(): void
    {
        $extension = new LatteExtension($this->container);
        $functionGet = $extension->getFunctions()['get'];

        $this->assertSame($this->container->get(BeginBody::class), $functionGet(BeginBody::class));
        $this->assertSame($this->container->get(EndBody::class), $functionGet(EndBody::class));
    }

    private function getContainer(): SimpleContainer
    {
        return new SimpleContainer([
            BeginBody::class => new BeginBody(),
            EndBody::class => new EndBody(),
        ]);
    }
}