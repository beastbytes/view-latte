<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Tests\Support\Providers;

use BeastBytes\View\Latte\Provider\FunctionProvider;

class TestFunctionProvider implements FunctionProvider
{
    public function getName(): string
    {
        return 'testFunction';
    }

    public function __invoke(int $number): int
    {
        return $number * 2;
    }
}