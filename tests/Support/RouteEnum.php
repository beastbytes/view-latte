<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Tests\Support;

enum RouteEnum: string
{
    public const PREFIX = 'item';

    case create = '/create';
    case delete = '/delete/{uuid}';
    case index = '';
    case update = '/update/{uuid}';
    case view = '/view/{uuid}';

    public function getName(?string $prefix = null): string
    {
        return ($prefix !== null ? $prefix . '_' : '') . $this->name;
    }
}