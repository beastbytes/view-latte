<?php

declare(strict_types=1);

use BeastBytes\View\Latte\Extension\LatteExtension;
use BeastBytes\View\Latte\Extension\UrlGeneratorExtension;
use BeastBytes\View\Latte\Tests\Support\BeginBody;
use BeastBytes\View\Latte\Tests\Support\EndBody;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class UrlGeneratorExtensionTest extends TestCase
{
    #[Test]
    public function getFunctions(): void
    {
        $extension = new UrlGeneratorExtension();
    }
}