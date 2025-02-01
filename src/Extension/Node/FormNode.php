<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Extension\Node;

use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

class FormNode extends StatementNode
{
    public ExpressionNode $action;
    public ExpressionNode $formName;

    public static function create(Tag $tag): self
    {
        $node = $tag->node = new self;
        $node->action = $tag->parser->parseExpression();
        $node->formName = $tag->parser->parseExpression();
        return $node;
    }

    public function print(PrintContext $context): string
    {
        // TODO: Implement print() method.
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): \Generator
    {
        // TODO: Implement getIterator() method.
    }
}