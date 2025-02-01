<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Tests\Support\Providers;

use BeastBytes\View\Latte\FilterProvider;

class TestFilter implements FilterProvider
{
    public function getName(): string
    {
        return 'test-filter';
    }

    public function __invoke(string $string): string
    {
        return strrev($string);
    }
}