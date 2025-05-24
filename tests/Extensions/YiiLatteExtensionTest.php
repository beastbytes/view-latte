<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Tests\Extensions;

use BeastBytes\View\Latte\Extensions\YiiLatte\YiiLatteExtension;
use BeastBytes\View\Latte\Tests\Support\BeginBody;
use BeastBytes\View\Latte\Tests\Support\EndBody;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class YiiLatteExtensionTest extends TestCase
{
    private string|SimpleContainer $container;

    #[Before]
    protected function setUp(): void
    {
        parent::setUp();
        $this->container = $this->getContainer();
    }

    #[Test]
    public function getFunction(): void
    {
        $extension = new YiiLatteExtension($this->container);
        $functions = $extension->getFunctions();
        $this->assertArrayHasKey('get', $functions);

        $this->assertSame($this->container->get(BeginBody::class), $functions['get'](BeginBody::class));
        $this->assertSame($this->container->get(EndBody::class), $functions['get'](EndBody::class));
    }

    private function getContainer(): SimpleContainer
    {
        return new SimpleContainer([
            BeginBody::class => new BeginBody(),
            EndBody::class => new EndBody(),
        ]);
    }
}