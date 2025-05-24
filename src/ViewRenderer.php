<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte;

use Throwable;
use Latte\Engine as Latte;
use Yiisoft\View\TemplateRendererInterface;
use Yiisoft\View\ViewInterface;

use function array_merge;
use function ob_end_clean;
use function ob_get_clean;
use function ob_get_level;
use function ob_implicit_flush;
use function ob_start;
use function str_replace;

/**
 * `ViewRenderer` allows using Latte with a View service.
 */
final class ViewRenderer implements TemplateRendererInterface
{
    public function __construct(private readonly Latte $latte)
    {
    }

    /**
     * @throws Throwable
     */
    public function render(ViewInterface $view, string $template, array $parameters): string
    {
        $latte = $this->latte;
        $renderer = function () use ($view, $template, $parameters, $latte): void {
            $template = str_replace('\\', '/', $template);

            $latte->render($template, array_merge($parameters, ['view' => $view]));
        };

        $obInitialLevel = ob_get_level();
        ob_start();
        ob_implicit_flush(false);

        try {
            /** @psalm-suppress PossiblyInvalidFunctionCall,PossiblyNullFunctionCall */
            $renderer->bindTo($view)();
            return ob_get_clean();
        } catch (Throwable $e) {
            while (ob_get_level() > $obInitialLevel) {
                ob_end_clean();
            }
            throw $e;
        }
    }
}