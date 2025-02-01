<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Tests\Support\Providers;

use BeastBytes\View\Latte\FunctionProvider;

class TestFunction implements FunctionProvider
{
    public function getName(): string
    {
        return 'test-function';
    }

    public function __invoke(int $number): int
    {
        return $number * 2;
    }
}