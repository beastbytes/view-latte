<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Extensions\Use;

use BeastBytes\View\Latte\Extensions\Use\Nodes\UseNode;
use Latte\Compiler\Node;
use Latte\Compiler\Nodes\Php\Expression\ClassConstantFetchNode;
use Latte\Compiler\Nodes\Php\Expression\NewNode;
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
            'applyUseTags' => self::order($this->applyUseTags(...), after: 'parseUseTags'),
            'parseUseTags' => self::order($this->parseUseTags(...), before: '*'),
        ];
    }

    public function applyUseTags(TemplateNode $templateNode): void
    {
        $resolved = array_keys($this->use);

        (new NodeTraverser)->traverse(
            $templateNode,
            enter: function (Node $node) use ($resolved): void {
                if (
                    (
                        $node instanceof NewNode
                        || $node instanceof ClassConstantFetchNode
                    )
                    && in_array($node->class->name, $resolved, true)
                ) {
                    $node->class->name = $this->use[$node->class->name];
                }
            }
        );
    }

    public function parseUseTags(TemplateNode $templateNode): void
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

                    $this->use[$resolved] = $fqcn;
                }
            },
        );
    }
}