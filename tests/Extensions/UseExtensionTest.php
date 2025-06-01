<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Tests\Extensions;

use BeastBytes\View\Latte\Extensions\Use\UseExtension;
use BeastBytes\View\Latte\Tests\TestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

final class UseExtensionTest extends TestCase
{
    #[BeforeClass]
    public static function beforeClass(): void
    {
        self::$extensions[] = new UseExtension();
        parent::beforeClass();
    }

    public static function classCallProvider()
    {
        return [
            'className' => [
                'classCall' => 'NamespacedClass',
                'expected' => '(new Framework\\Module\\NamespacedClass)',
            ],
            'classNameWithParenthesis' => [
                'classCall' => 'NamespacedClass()',
                'expected' => '(new Framework\\Module\\NamespacedClass)',
            ],
            'classNameWithAParameters' => [
                'classCall' => 'NamespacedClass(5)',
                'expected' => '(new Framework\\Module\\NamespacedClass(5))',
            ],
            'classNameWithParameters' => [
                'classCall' => 'NamespacedClass(5, $b)',
                'expected' => '(new Framework\\Module\\NamespacedClass(5, $b))',
            ],
        ];
    }

    #[Test]
    #[DataProvider('classCallProvider')]
    public function use_tag_new_class(string $classCall, string $expected): void
    {
        $template = sprintf(
            <<<'TEMPLATE'
                {use Framework\Module\NamespacedClass}
                
                <p>The value is {(new %s)->getValue()}</p>
                TEMPLATE,
            $classCall
        );

        $actual = $this->compile($template);
        $this->assertStringContainsString($expected, $actual);
    }

    #[Test]
    public function use_tag_class_constant(): void
    {
        $expected = '(Framework\\Module\\NamespacedClass::CONSTANT)';
        $template = <<<'TEMPLATE'
            {use Framework\Module\NamespacedClass}
            
            <p>The constant is {NamespacedClass::CONSTANT}</p>
            TEMPLATE;

        $actual = $this->compile($template);
        $this->assertStringContainsString($expected, $actual);
    }

    #[Test]
    public function use_tag_in_filter(): void
    {
        $expected = '($this->filters->replace)($testString, Framework\\Module\\NamespacedClass::CONSTANT)';
        $template = <<<'TEMPLATE'
            {use Framework\Module\NamespacedClass}
            
            {varType string $testString}
            
            <p>{$testString|replace: NamespacedClass::CONSTANT}</p>
            TEMPLATE;

        $actual = $this->compile($template);
        $this->assertStringContainsString($expected, $actual);
    }

    #[Test]
    /**
     * Use of an alias is about resolving the fully qualified class name, so no need to test all replacement scenarios.
     */
    public function use_tag_alias(): void
    {
        $expected = '(Framework\\Module\\NamespacedClass::CONSTANT)';
        $template = <<<'TEMPLATE'
            {use Framework\Module\NamespacedClass AliasedClass}

            <p>The constant is {AliasedClass::CONSTANT}</p>
            TEMPLATE;

        $actual = $this->compile($template);
        $this->assertStringContainsString($expected, $actual);
    }

    #[Test]
    public function multiple_use_tags(): void
    {
        $expected = [
            '(new Framework\\Module\\NamespacedClass)',
            '(Framework\Module\\NamespacedClass::CONSTANT)',
            '($this->filters->replace)($testString, Framework\\Module\\Aliased\\NamespacedClass::CONSTANT)',
        ];
        $template = <<<'TEMPLATE'
            {use Framework\Module\Aliased\NamespacedClass AliasedClass}
            {use Framework\Module\NamespacedClass}
            
            {varType string $testString}
            
            <p>The value is {(new NamespacedClass())->getValue()}</p>
            <p>The constant is {NamespacedClass::CONSTANT}</p>
            <p>{$testString|replace: AliasedClass::CONSTANT}</p>
            TEMPLATE;

        $actual = $this->compile($template);

        foreach ($expected as $needle) {
            $this->assertStringContainsString($needle, $actual);
        }
    }

    private function compile(string $template): string
    {
        $templateFile = self::LATTE_TEMPLATE_DIR . DIRECTORY_SEPARATOR . '_' . md5($template) . '.latte';
        file_put_contents($templateFile, $template);
        return self::$latte->compile($templateFile);
    }
}