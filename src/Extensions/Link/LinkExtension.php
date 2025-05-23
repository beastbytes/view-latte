<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Extensions\Link;

final class LinkExtension
{
    public function getFunctions(): array
    {
        return [
            'link' => ''
        ];
    }

    public function getTags(): array
    {
        return [
            'n:href' => Nodes\LinkNode::create(...),
            'link' => Nodes\LinkNode::create(...),
        ];
    }
}