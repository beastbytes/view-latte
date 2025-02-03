This package is an extension of the [Yii View Rendering Library](https://github.com/yiisoft/view/). This extension
provides a `ViewRender` that allows use of the [Latte](https://latte.nette.org/) view template engine.

`Latte` has some advantages over `Twig` as a templating engine:
* The major advantage of Latte is that it is PHP (Twig is Python), this makes writting Latte templates simpler
and debugging them (if you need to) a lot simpler.
* Use [PHP expressions in templates](https://latte.nette.org/en/syntax#toc-latte-understands-php) 
* Better defence against XSS (Cross Site Scripting)
* Excellent plugin for PhpStorm that enables type hints, code completion, etc.

## Requirements
- PHP 8.1 or higher.

## Installation
Install the package using [Composer](https://getcomposer.org):

Either:
```shell
composer require beastbytes/view-latte
```
or add the following to the `require` section of your `composer.json`
```json
"beastbytes/view-latte": "*"
```

## Configuration
In order to register `Latte` as the renderer in `WebView`, the `beastbytes/view-latte` package must override 
the `yiisoft\view` package configuration. To do this, add `beastbytes/view-latte` to the `vendor-override-layer`
option in `config-plugin-options`; this is either in the `extra` section of your root `composer.json`
or in your external configuration file.

### composer.json
```json
"extra": {
    "config-plugin-options": {
        "vendor-override-layer": [
          "beastbytes/view-latte"
        ]
    },
    "config-plugin": {
        // ...
    }
}
```
### External Configuration File
```php
'config-plugin-options' => [
    // other config-plugin-options
    'vendor-override-layer' => [
        'beastbytes/view-latte',
        // other vendor overrides
    ]    
],
```
_Note:_ if `beastbytes/view-latte` is the only vendor override,
it can be specified as a string in both of the configuration formats.

### Params
The `beastbytes/view-latte` package supports the addition of user defined filters, functions, and extensions to `Latte`.
Each filter and function is defined in its own class; extensions are packages.

To add them to `Latte` specify them in the `filterProviders`, `functionProviders`, and `extensions` keys
of `beastbytes/view-latte` in the `params` array.

```php
'beastbytes/view-latte' => [
    'filterProviders' => [
        new MyLatteFilter()
    ],
    'functionProviders' => [
        new MyLatteFunction()
    ],
    'extensions' => [
        new myLatteExtension()
    ]
],
```

See [User Defined Filters and Functions](#user-defined-filters-and-functions) 
for details on how to define filters and functions.

See [Creating an Extension](https://latte.nette.org/en/creating-extension) for details on how to create an extension.

## Templates
As you would expect, all the variables defined when calling the view renderer's `render()` method in an action are
available in the template, as are injected variables.

The Latte extension provides access to everything defined in the application container 
in all view templates and layouts.

#### Basic Layout & View Templates
**Note:** A major difference between `Latte` and `PHP` templates is the definition of `$this`.
* `$this` in Latte templates is the Latte template being rendered
* `$view` is the WebView instance

```latte
{varType string $content}
{varType Yiisoft\View\WebView $view}

{do $assetManager->register('App\Asset\AppAsset')}
{do $view->addCssFiles($assetManager->getCssFiles())}
{do $view->addCssStrings($assetManager->getCssStrings())}
{do $view->addJsFiles($assetManager->getJsFiles())}
{do $view->addJsStrings($assetManager->getJsStrings())}
{do $view->addJsVars($assetManager->getJsVars())}

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

<p class="subtitle">Let's start something great with <strong>Yii3 &amp; Latte</strong>!</p>

<p class="subtitle is-italic">
    <a href="https://github.com/yiisoft/docs/tree/master/guide/en" target="_blank" rel="noopener">
        Don't forget to check the Yii guide 
    </a>
    <a href="https://latte.nette.org/en/guide" target="_blank" rel="noopener">
        and the Latte documentation.
    </a>    
</p>
```

## User Defined Filters and Functions
The `view-latte` package supports the addition of user defined filters and functions to the Latte Engine;
see [Configuration -> Params](#params) for details on how to specify them. This section details how to define them.

Each filter and/or function is defined in its own class. Filters must implement the FilterProviderinterface
and functions the FunctionProviderinterface; both **_must_** implement the `__invoke()` method to provide their
functionality.

### Example Filter

```php
<?php

declare(strict_types=1);

namespace App\Latte\Providers;

use BeastBytes\View\Latte\Provider\FilterProvider;

class MyLatteFilter implements FilterProvider
{
    public function getName(): string
    {
        return 'myLatteFilter'; // the name registered with Latte and used to invoke the filter in templates
    }

    public function __invoke(string $string): string
    {
        return strrev($string);
    }
}
```

### Example Function

```php
<?php

declare(strict_types=1);

namespace App\Latte\Providers;

use BeastBytes\View\Latte\Provider\FunctionProvider;

class MyLatteFunction implements FunctionProvider
{
    public function getName(): string
    {
        return 'myLatteFunction'; // the name registered with Latte and used to call the function in templates
    }

    public function __invoke(int $number): int
    {
        return $number * 2;
    }
}
```

### Example Template
```latte
{var int $x = 2}
{var string $strig = 'ABCDE'}

<p>{$strig|myLatteFilter}</p> <!-- will output EDCBA -->
<p>{=myLatteFunction($x)}</p> <!-- will output 4 -->
```

## License
The BeastBytes View Latte Renderer is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.