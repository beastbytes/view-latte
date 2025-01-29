<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Tests\Support;

final class ErrorContent
{
    public function content(): string
    {
        ob_start();
        throw new RuntimeError('test');
    }
}