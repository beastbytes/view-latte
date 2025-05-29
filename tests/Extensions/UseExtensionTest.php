<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Tests\Extensions;

use BeastBytes\View\Latte\Extensions\Use\UseExtension;
use BeastBytes\View\Latte\Tests\TestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\Test;

final class UseExtensionTest extends TestCase
{
    #[BeforeClass]
    public static function beforeClass(): void
    {
        self::$extensions[] = new UseExtension();
        parent::beforeClass();
    }

    #[Test]
    public function use_statement(): void
    {
        $expected = <<<'EXPECTED'
            $namespacedClass = new Framework\\Module\\NamespacedClass();
            $namespacedConstant = Framework\\Module\\NamespacedClass::CONSTANT;
            EXPECTED;
        $template = <<<'TEMPLATE'
            {use Framework\Module\NamespacedClass}
            
            $namespacedClass = new NamespacedClass();
            $namespacedConstant = NamespacedClass::CONSTANT;
            TEMPLATE;
        $this->assert($expected, $template);
    }

    #[Test]
    public function use_as_statement(): void
    {
        $expected = <<<'EXPECTED'
            $aliasedClass = new Framework\\Module\\NamespacedClass();
            $aliasedConstant = Framework\\Module\\NamespacedClass::CONSTANT;
            EXPECTED;
        $template = <<<'TEMPLATE'
            {use Framework\Module\NamespacedClass AliasedClass}
            
            $aliasedClass = new AliasedClass();
            $aliasedConstant = AliasedClass::CONSTANT;
            TEMPLATE;
        $this->assert($expected, $template);
    }

    #[Test]
    public function multiple_statements(): void
    {
        $expected = <<<'EXPECTED'
            $aliasedClass = new Framework\\Module\\Aliased\\NamespacedClass();
            $aliasedConstant = Framework\\Module\\Aliased\\NamespacedClass::CONSTANT;
            $namespacedClass = new Framework\\Module\\NamespacedClass();
            $namespacedConstant = Framework\\Module\\NamespacedClass::CONSTANT;
            EXPECTED;
        $template = <<<'TEMPLATE'
            {use Framework\Module\Aliased\NamespacedClass AliasedClass}
            {use Framework\Module\NamespacedClass}
            
            $aliasedClass = new AliasedClass();
            $aliasedConstant = AliasedClass::CONSTANT;
            $namespacedClass = new NamespacedClass();
            $namespacedConstant = NamespacedClass::CONSTANT;
            TEMPLATE;
        $this->assert($expected, $template);
    }

    private function assert(string $expected, string $template): void
    {
        $templateFile = self::LATTE_TEMPLATE_DIR . DIRECTORY_SEPARATOR . '_' . md5($template) . '.latte';

        file_put_contents($templateFile, $template);

        $actual = self::$latte->compile($templateFile);

        $this->assertStringContainsString($expected, $actual);
    }
}