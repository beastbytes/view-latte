<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Tests;

use BeastBytes\View\Latte\ViewRenderer;
use Latte\CompileException;
use Latte\Engine as Latte;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Files\FileHelper;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\View\TemplateRendererInterface;
use Yiisoft\View\WebView;

final class ViewTest extends TestCase
{
    private string $layoutPath;
    private SimpleContainer $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = $this->getContainer();
        FileHelper::ensureDirectory($this->container->get(Aliases::class)->get('@tmp'));
        $this->layoutPath = $this->container->get(Aliases::class)->get('@views') . '/layout.latte';
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        FileHelper::removeDirectory($this->container->get(Aliases::class)->get('@tmp'));
    }

    #[Test]
    public function renderView(): void
    {
        $tag = [
            'name' => 'testContent',
            'value' => 'Latte template view renderer'
        ];

        $content = $this
            ->getView()
            ->render('test.latte', [$tag['name'] => $tag['value']])
        ;

        $result = $this
            ->getView()
            ->render($this->layoutPath, ['content' => $content])
        ;

        $this->assertStringContainsString('Header content', $result);
        $this->assertStringContainsString('Footer content', $result);
        $this->assertStringContainsString($tag['value'], $result);
        $this->assertStringNotContainsString('{$' . $tag['name'] . '}', $result);
    }

    #[Test]
    public function exceptionDuringRendering(): void
    {
        $view = $this->getView();
        $renderer = $this->container->get(TemplateRendererInterface::class);

        $obInitialLevel = ob_get_level();

        try {
            $renderer->render($view, $this->container->get(Aliases::class)->get('@views') . '/error.latte', []);
        } catch (CompileException) {
        }

        $this->assertSame(ob_get_level(), $obInitialLevel);
    }

    private function getContainer(): SimpleContainer
    {
        $aliases = new Aliases([
            '@root' => dirname(__DIR__),
            '@public' => '@root/tests/public',
            '@basePath' => '@public/assets',
            '@tmp' => '@public/tmp',
            '@views' => '@public/views',
            '@baseUrl' => '/baseUrl',
        ]);

        $latte = new Latte;
        $latte->setTempDirectory($aliases->get('@tmp'));

        return new SimpleContainer([
            Aliases::class => $aliases,
            Latte::class => $latte,
            TemplateRendererInterface::class => new ViewRenderer($latte),
        ]);
    }

    private function getView(): WebView
    {
        return (new WebView($this->container->get(Aliases::class)->get('@views')))
            ->withRenderers(['latte' => new ViewRenderer($this->container->get(Latte::class))])
            ->withFallbackExtension('latte')
        ;
    }
}