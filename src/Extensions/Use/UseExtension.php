<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Extensions\Use;

use BeastBytes\View\Latte\Extensions\Use\Nodes\UseNode;
use Latte\Compiler\Node;
use Latte\Compiler\Nodes\TemplateNode;
use Latte\Compiler\Nodes\TextNode;
use Latte\Compiler\NodeTraverser;
use Latte\Extension;

final class UseExtension extends Extension
{
    private array $use = [];

    public function getTags(): array
    {
        return [
            'use' => Nodes\UseNode::create(...),
        ];
    }

    public function getPasses(): array
    {
        return [
            'applyUsePass' => $this->applyUse(...),
        ];
    }

    public function applyUse(TemplateNode $templateNode): void
    {
        (new NodeTraverser)->traverse(
            $templateNode,
            enter: function (Node $node): void {
                if ($node instanceof UseNode) {
                    $fqcn = $node->fqcn->content;

                    if ($node->alias instanceof TextNode) {
                        $resolved = $node->alias->content;
                    } else {
                        $resolved = mb_substr($fqcn, mb_strrpos($fqcn, '\\') + 1);
                    }

                    $this->use[' ' . $resolved] = ' ' . $fqcn;
                } elseif ($node instanceof TextNode) {
                    $node->content = str_replace(array_keys($this->use), array_values($this->use), $node->content);
                }
            },
        );
    }
}