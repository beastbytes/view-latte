<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Tests;

use BeastBytes\View\Latte\Extension\UrlGeneratorExtension;
use BeastBytes\View\Latte\LatteFactory;
use Generator;
use Latte\Engine;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Yiisoft\Files\FileHelper;
use Yiisoft\Router\FastRoute\UrlGenerator;
use Yiisoft\Router\Route;
use Yiisoft\Router\RouteCollection;
use Yiisoft\Router\RouteCollector;

final class UrlGeneratorExtensionTest extends TestCase
{
    protected const CACHE_DIR = __DIR__ . '/public/cache';
    protected const TEMPLATE_DIR = __DIR__ . '/public/views';

    protected Engine $latte;

    protected function setUp(): void
    {
        parent::setUp();
        FileHelper::ensureDirectory(self::CACHE_DIR);

        $route = Route::get('/test/url')
            ->name('test-url')
            ->action(fn() => null)
        ;
        $collector = new RouteCollector();
        $routeCollection = new RouteCollection($collector->addRoute($route));
        $urlGenerator = new UrlGenerator($routeCollection);

        $factory = new LatteFactory(
            self::CACHE_DIR,
            extensions: [new UrlGeneratorExtension($urlGenerator)]
        );
        $this->latte = $factory->create();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        FileHelper::removeDirectory(self::CACHE_DIR);
    }

    #[Test]
    #[DataProvider('generateUrlProvider')]
    public function generateUrl(string $expected): void
    {
        $html = $this
            ->latte
            ->renderToString(self::TEMPLATE_DIR . '/url.latte')
        ;

        $this->assertStringContainsString($expected, $html);
    }

    public static function generateUrlProvider(): Generator
    {
        yield 'h tag' => ['expected' => '<a href="/test/url">Test URL</a>'];
        yield 'link tag' => ['expected' => '<p class="tag">/test/url</p>'];
        yield 'link filter' => ['expected' => '<span class="filter">/test/url</span>'];
    }
}