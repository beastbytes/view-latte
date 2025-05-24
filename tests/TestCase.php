<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Tests;

use BeastBytes\View\Latte\LatteFactory;
use Latte\Engine;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;
use Yiisoft\Files\FileHelper;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected const LATTE_TEMPLATE_DIR = __DIR__ . '/generated/latte/template';
    private const LATTE_CACHE_DIR = __DIR__ . '/generated/latte/cache';

    protected static array $extensions = [];
    protected static Engine $latte;

    #[BeforeClass]
    public static function beforeClass(): void
    {
        FileHelper::ensureDirectory(self::LATTE_CACHE_DIR);
        FileHelper::ensureDirectory(self::LATTE_TEMPLATE_DIR);

        $factory = new LatteFactory(
            self::LATTE_CACHE_DIR,
            extensions: self::$extensions
        );
        self::$latte = $factory->create();
    }

    #[AfterClass]
    public static function afterClass(): void
    {
        FileHelper::removeDirectory(self::LATTE_CACHE_DIR);
        FileHelper::removeDirectory(self::LATTE_TEMPLATE_DIR);
    }
}