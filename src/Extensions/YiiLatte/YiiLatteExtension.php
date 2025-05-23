<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Extensions\YiiLatte;

use Psr\Container\ContainerInterface;

final class YiiLatteExtension
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    public function getFunctions(): array
    {
        return [
            'get' => fn (string $id): mixed => $this->container->get($id),
        ];
    }
}