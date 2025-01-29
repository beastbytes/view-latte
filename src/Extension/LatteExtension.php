<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Extension;

use Latte\Extension;
use Psr\Container\ContainerInterface;

class LatteExtension extends Extension
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function getFunctions(): array
    {
        return [
            'get' => fn (string $id): mixed => $this->container->get($id),
        ];
    }
}