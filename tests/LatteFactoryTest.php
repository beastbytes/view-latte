<?php

namespace BeastBytes\View\Latte\Tests;

use BeastBytes\View\Latte\LatteFactory;
use BeastBytes\View\Latte\Tests\Support\Providers\TestFilterProvider;
use BeastBytes\View\Latte\Tests\Support\Providers\TestFunctionProvider;
use Latte\Engine;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LatteFactoryTest extends TestCase
{
    private string $cacheDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheDir = __DIR__ . '/public/tmp';
    }

    #[Test]
    public function create(): void
    {
        $factory = new LatteFactory($this->cacheDir);
        $latte = $factory->create();

        $this->assertInstanceOf(Engine::class, $latte);
    }

    #[Test]
    public function createWithProviders(): void
    {
        $factory = new LatteFactory(
            $this->cacheDir,
            [new TestFilterProvider],
            [new TestFunctionProvider],
        );
        $latte = $factory->create();

        $filters = $latte->getFilters();
        $functions = $latte->getFunctions();

        $this->assertArrayHasKey('testFilter', $filters);
        $this->assertArrayHasKey('testFunction', $functions);
    }

    #[Test]
    public function providers(): void
    {
        $factory = new LatteFactory(
            $this->cacheDir,
            [new TestFilterProvider],
            [new TestFunctionProvider],
        );
        $latte = $factory->create();

        $result = $latte->renderToString(__DIR__ . '/public/views/providers.latte');

        $this->assertStringContainsString('<p>EDCBA</p>', $result);
        $this->assertStringContainsString('<p>4</p>', $result);
    }
}
