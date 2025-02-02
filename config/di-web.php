<?php

declare(strict_types=1);

use BeastBytes\View\Latte\Extension\LatteExtension;
use BeastBytes\View\Latte\LatteFactory;
use BeastBytes\View\Latte\ViewRenderer;
use Latte\Engine as Latte;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\View\WebView;

/** @var array $params */

return [
    Latte::class => static function (ContainerInterface $container) use ($params): Latte {
        $latte = (new LatteFactory(
            (string) $container
                ->get(Aliases::class)
                ->get('@runtime/cache/latte')
            ,
            $params['beastbytes/view-latte']['filterProviders'],
            $params['beastbytes/view-latte']['functionProviders'],
        ))
            ->create()
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