This package is an extension of the [Yii View Rendering Library](https://github.com/yiisoft/view/). This extension
provides a `ViewRender` that allows use of the [Latte](https://latte.nette.org/) view template engine.

`Latte` has some advantages over `Twig` as a templating engine:
* The major advantage of Latte is that it is PHP (Twig is Python), this makes writing Latte templates simpler
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

## View-Latte Extensions
The `view-latte` package contains the following extensions:

### CacheExtension
**NOTE: CacheExtension is not implemented yet**

The `CacheExtension` allows caching of view fragments and dynamic content within cached fragments.
The extension provides the `cache` and `dynamic` tags.
It is enabled if the DI container contains a `Yiisoft\Cache\CacheInterface` implementation.

**NOTE:** Templates using caching _must_ have the variable `$cache`,
a concrete instance of  `Yiisoft\Cache\CacheInterface`, defined
```latte
{varType Yiisoft\Cache\CacheInterface $cache}
```

#### {cache} Tag
The `cache` tag allows a template or part of a template to be cached. The tag has three parameters:
* ttl (int) &ndash; The TTL of the cached content in seconds. Default is `60`.
* dependency (Yiisoft\Cache\Dependency\Dependency|null) &ndash; The dependency of the cached content. 
Default is `null`.
* beta (float) &ndash; The value for calculating the range that's used for "Probably early expiration".
Default is `1.0`.

##### Examples
###### Basic Usage
```latte
{cache}
    content to be cached
{/cache}
```
###### Set TTL
```latte
{cache 3600}
content to be cached
{/cache}
```
###### Set a Dependency
```latte
{cache 3600, new Yiisoft\Cache\Dependency\TagDependency('fragment-1')}
    content to be cached
{/cache}
...
{if $theAnswerToTheUltimateQuestionOfLife !== 42}
    {do Yiisoft\Cache\Dependency\TagDependency::invalidate('fragment-1')}
{/if}
```

#### {dynamic} Tag
The `dynamic` tag defines dynamic content within a `cache` tag. The tag has three parameters:
* contentGenerator (callable) &ndash; a callable that generates the dynamic content;
it has the signature `function (array $parameters = []): string;`
* parameters (array) &ndash; parameters as key=>value pairs passed to contentGenerator
```latte
{dynamic function (array $parameters = []): string {return $generatedContent}, ['a' => 1, 'b' => 2]}
```

There is no limit to the number of `dynamic` tags within a `cache` tag.

### LinkExtension
The `LinkExtension` allows generation of URL using routes.
The extension provides the `n:href` n:attribute and `link` tag to generate URLs.
It is enabled if the DI container contains a `Yiisoft\Router\UrlGeneratorInterface` implementation.

#### n:href Attribute
The `n:href` attribute is used to generate URLs in `<a/>` tags;
its parameters are the same as `Yiisoft\Router\UrlGeneratorInterface::generate().
```latte
<a n:href="route/name, arguments, queryParameters">Content</a>
```

#### n:action Attribute
The `n:action` attribute is used to generate the action URL in `<form/>` tags;
its parameters are the same as `Yiisoft\Router\UrlGeneratorInterface::generate().
```latte
<form n:action="route/name, arguments, queryParameters">
    // Form controls
</form>
```

**Tip** The [form-latte extension](https://github.com/beastbytes/form-latte) 
integrates the Yii Framework Form package with view-latte.

#### {link} Tag
The `{link}` tag is used to print a URL;
its parameters are the same as `Yiisoft\Router\UrlGeneratorInterface::generate().
```latte
{link route/name, arguments, queryParameters}
```

### UseExtension
The `UseExtension` emulates PHP's `use` operator and allows writing cleaner templates.
The extension provides provides the `use` tag.
It is always enabled.

By default, Latte templates require the use of Fully Qualified CLass Names (FQCN); this can lead to cluttered templates.
The `use` tag emulates PHP's `use` operator and allows templates to define the FQCN and optionally an alias,
and refer to the _used_ class by the alias or base class name.

#### Using Namespaced Classes in Latte
The extension replaces the alias or base class name in the `use` tag with the FQCN in the cached template;
it _does not_ import or alias the class.

#### {use} Tag
```latte
{use Framework\Module\NamespacedClass}

<p>The value is {(new NamespacedClass)->getValue()}</p>
<p>The constant is {NamespacedClass::CONSTANT}</p>
```

#### {use} Tag with Alias
To specify an alias, put the alias after the FQCN; unlike PHP, there is no _as_ clause.
```latte
{use Framework\Module\Aliased\NamespacedClass AliasedClass}

<p>The value is {(new AliasedClass())->getValue()}</p>
<p>The constant is {AliasedClass::CONSTANT}</p>
```

#### Multiple {use} Tags
```latte
{use Framework\Module\Aliased\NamespacedClass AliasedClass}
{use Framework\Module\NamespacedClass}

{varType string $testString}

<p>The value is {(new NamespacedClass())->getValue()}</p>
<p>The constant is {NamespacedClass::CONSTANT}</p>
<p>{$testString|replace: AliasedClass::CONSTANT}</p>
```

### YiiLatteExtension
The `YiiLatteExtension` allows access to any package in the DI container.
The extension provides the `get` function which takes the id of the required package as a parameter.
It is always enabled.

#### get() Function
```latte
{do $package = get('PackageId')}
```

## Other Extensions
### TranslatorExtension
Latte's Translation Extension is enabled if 
the DI container contains a `Yiisoft\Translator\TranslatorInterface` implementation, 
allowing use of Latte's Translation [tags](https://latte.nette.org/en/tags#toc-translation)
and [filter](https://latte.nette.org/en/filters#toc-translate) in templates.

### RawPhpExtension
Latte's `RawPhpExtension` is _not_ enabled by default. To enable it, add it to the `extensions` section
of the `beastbytes/view-latte` in your configuration parameters:

```php
return [
    'beastbytes/view-latte' => [
        'extensions' => [
            new \Latte\Essential\RawPhpExtension(),
            // Other extensions
        ],
        // Other view-latte configuration
    ],
]
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