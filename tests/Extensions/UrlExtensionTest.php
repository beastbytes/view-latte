<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Tests\Extensions;

use BeastBytes\View\Latte\Extensions\Url\UrlExtension;
use BeastBytes\View\Latte\Tests\Support\RouteConst;
use BeastBytes\View\Latte\Tests\Support\RouteEnum;
use BeastBytes\View\Latte\Tests\TestCase;
use Generator;
use HttpSoft\Message\Uri;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\FastRoute\UrlGenerator;
use Yiisoft\Router\Route;
use Yiisoft\Router\RouteCollection;
use Yiisoft\Router\RouteCollector;
use Yiisoft\Router\UrlGeneratorInterface;

final class UrlExtensionTest extends TestCase
{
    private const URL = 'https://example.com';

    private static RouteCollection $routeCollection;
    private static UrlGeneratorInterface $urlGenerator;

    #[BeforeClass]
    public static function beforeClass(): void
    {
        $routes = [
            Route::get('/test/item')
                ->name('item_index')
                ->action(fn() => null)
            ,
            Route::get('/test/item/create')
                ->name('item_create')
                ->action(fn() => null)
            ,
            Route::get('/test/item/update/{id:\d+}')
                ->name('item_update')
                ->action(fn() => null)
            ,
            Route::get('/test/item/{id:\d+}')
                ->name('item_view')
                ->action(fn() => null)
            ,
        ];

        self::$routeCollection = new RouteCollection((new RouteCollector())->addRoute(...$routes));

        $currentRoute = new CurrentRoute();
        $currentRoute->setUri(new Uri(self::URL));;

        self::$urlGenerator = new UrlGenerator(self::$routeCollection, $currentRoute);

        self::$extensions[] = new UrlExtension(self::$urlGenerator);
        parent::beforeClass();
    }

    #[Test]
    #[DataProvider('routeProvider')]
    public function n_action_attribute(string $name, array $arguments): void
    {
        $expected = sprintf(
            '<form action="%s">',
            self::$urlGenerator->generate($name, $arguments)
        );
        $template = '<form n:action="%s, [%s]">';
        $this->assert($expected, $template, $name, $arguments);
    }

    #[Test]
    #[DataProvider('routeProvider')]
    public function n_href_attribute(string $name, array $arguments): void
    {
        $expected = sprintf(
            '<a href="%s">Test</a>',
            self::$urlGenerator->generate($name, $arguments)
        );
        $template = '<a n:href="%s, [%s]">Test</a>';
        $this->assert($expected, $template, $name, $arguments);
    }

    #[Test]
    public function n_href_attribute_expression(): void
    {
        $expected = sprintf(
            '<a href="%s">Test</a>',
            self::$urlGenerator->generate(RouteEnum::create->getName(RouteEnum::PREFIX))
        );
        $template = '<a n:href="\\BeastBytes\\View\\Latte\\Tests\\Support\\RouteEnum::create->getName(\\BeastBytes\\View\\Latte\\Tests\\Support\\RouteEnum::PREFIX), []">Test</a>';
        $templateFile = self::LATTE_TEMPLATE_DIR . DIRECTORY_SEPARATOR . '_' . md5($template) . '.latte';
        file_put_contents($templateFile, $template);
        $actual = self::$latte->renderToString($templateFile);
        $this->assertSame($expected, $actual);

        $expected = sprintf(
            '<a href="%s">Test</a>',
            self::$urlGenerator->generate(RouteEnum::view->getName(RouteEnum::PREFIX), ['id' => '69'])
        );
        $template = '<a n:href="\\BeastBytes\\View\\Latte\\Tests\\Support\\RouteEnum::view->getName(\\BeastBytes\\View\\Latte\\Tests\\Support\\RouteEnum::PREFIX), [\'id\' => \'69\']">Test</a>';
        $templateFile = self::LATTE_TEMPLATE_DIR . DIRECTORY_SEPARATOR . '_' . md5($template) . '.latte';
        file_put_contents($templateFile, $template);
        $actual = self::$latte->renderToString($templateFile);
        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function n_href_attribute_constant(): void
    {
        $expected = sprintf(
            '<a href="%s">Test</a>',
            self::$urlGenerator->generate(RouteConst::CREATE)
        );
        $template = '<a n:href="\\BeastBytes\\View\\Latte\\Tests\\Support\\RouteConst::CREATE">Test</a>';
        $templateFile = self::LATTE_TEMPLATE_DIR . DIRECTORY_SEPARATOR . '_' . md5($template) . '.latte';
        file_put_contents($templateFile, $template);
        $actual = self::$latte->renderToString($templateFile);
        $this->assertSame($expected, $actual);
    }

    /*
    #[Test]
    public function n_href_attribute_variable(): void
    {
        $routeName = RouteEnum::create->getName(RouteEnum::PREFIX);
        $expected = sprintf(
            '<a href="%s">Test</a>',
            self::$urlGenerator->generate($routeName)
        );
        $template = sprintf(
            <<<'Template'
                {var string $routeName = ''}
                {do $routeName = %s}
                <a n:href="$routeName">Test</a>
                Template,
            $routeName
        );
        $templateFile = self::LATTE_TEMPLATE_DIR . DIRECTORY_SEPARATOR . '_' . md5($template) . '.latte';
        file_put_contents($templateFile, $template);
        $actual = self::$latte->renderToString($templateFile);
        $this->assertSame($expected, $actual);
    }
    */

    #[Test]
    #[DataProvider('routeProvider')]
    public function link_tag(string $name, array $arguments): void
    {
        $expected = self::$urlGenerator->generateAbsolute($name, $arguments);
        $template = '{link %s, [%s]}';
        $this->assert($expected, $template, $name, $arguments);
    }

    public static function routeProvider(): Generator
    {
        yield [
            'name' => 'item_index',
            'arguments' => [],
        ];
        yield [
            'name' => 'item_create',
            'arguments' => [],
        ];
        yield [
            'name' => 'item_update',
            'arguments' => ['id' => '69'],
        ];
        yield [
            'name' => 'item_view',
            'arguments' => ['id' => '69'],
        ];
    }

    private function assert(string $expected, string $template, string $routeName, array $routeArguments): void
    {
        $arguments = '';
        foreach ($routeArguments as $key => $value) {
            $arguments .= $key . ' => ' . $value . ', ';
        }

        $template = sprintf(
            $template,
            $routeName,
            rtrim($arguments, ', ')
        );
        $templateFile = self::LATTE_TEMPLATE_DIR . DIRECTORY_SEPARATOR . '_' . md5($template) . '.latte';

        file_put_contents($templateFile, $template);

        $actual = self::$latte->renderToString($templateFile);

        $this->assertSame($expected, $actual);
    }
}