This package is an extension of the [Yii View Rendering Library](https://github.com/yiisoft/view/). This extension
provides a `ViewRender` that allows use of the [Latte](https://latte.nette.org/) view template engine.

## Requirements

- PHP 8.1 or higher.

## Installation

The package could be installed with [Composer](https://getcomposer.org):

```shell
composer require beastbytes/view-latte
```

## Usage
In your application, specify the configuration for `Latte` and override the configuration for `WebView`:

```php
use BeastBytes\View\Latte\ViewRenderer;
use BeastBytes\View\Latte\Extension\LatteExtension;
use Latte\Engine as Latte;
use Loytyi\ViewRenderer;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\View\WebView;

/** @var array $params */

return [
    Latte::class => static function (ContainerInterface $container): Latte {
        $latte = new Latte;
        $latte->setTempDirectory((string) $container
            ->get(Aliases::class)
            ->get('@runtime/cache/latte'))
        ;
        $latte->addExtension(new LatteExtension($container));

        return $latte;
    },
    WebView::class => static function (ContainerInterface $container) use ($params): WebView {
        $webView = new WebView(
            $container
                ->get(Aliases::class)
                ->get('@views'),
            $container->get(EventDispatcherInterface::class),
        );

        $webView = $webView
            ->withFallbackExtension('latte')
            ->withRenderers(['latte' => new ViewRenderer($container->get(Latte::class))])
        ;

        $webView->setParameters($params['yiisoft/view']['parameters']);
        return $webView;
    },
];
```

### Template

All variables that were in the regular template are also available in the Latte template.

The Latte extension provides access to everything defined in the container;
it is available in all view templates and layouts.

#### Basic Layout & View Templates

**Note:**
* $this in Latte templates is the Latte template
* $view is the WebView class

```latte
{varType Yiisoft\View\WebView $view}

{$view->beginPage()|noescape}
<!DOCTYPE html>
<html lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{$view->getTitle()}</title>
        {$view->head()|noescape}
    </head>
    <body>
        {$view->beginBody()|noescape}
        {include 'header.latte'}
        <main role="main" class="container py-4">
            {$content|noescape}
        </main>
        {include 'footer.latte'}
        {$view->endBody()|noescape}
    </body>
</html>
{$view->endPage(true)|noescape}
```

And a view template will be:

```latte
{varType App\ApplicationParameters $applicationParameters}
{varType Yiisoft\View\WebView $view}

{do $view->setTitle($applicationParameters->getName())}

<h1 class="title">Hello!</h1>

<p class="subtitle">Let's start something great with <strong>Yii3</strong>!</p>

<p class="subtitle is-italic">
    <a href="https://github.com/yiisoft/docs/tree/master/guide/en" target="_blank" rel="noopener">
        Don't forget to check the guide.
    </a>
</p>
```

## License

The BeastBytes View Latte Renderer is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.