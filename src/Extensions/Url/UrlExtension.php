<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Extensions\Url;

use Latte\CompileException;
use Latte\Compiler\NodeHelpers;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\Expression\ClassConstantFetchNode;
use Latte\Compiler\Nodes\Php\Expression\ConstantFetchNode;
use Latte\Compiler\Nodes\Php\Expression\MethodCallNode;
use Latte\Compiler\Nodes\Php\Expression\VariableNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use Latte\Essential\Nodes\PrintNode;
use Latte\Extension;
use Yiisoft\Router\UrlGeneratorInterface;

final class UrlExtension extends Extension
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getTags(): array
    {
        return [
            'n:action' => [$this, 'parseUrl'],
            'n:href' => [$this, 'parseUrl'],
            'n:src' => [$this, 'parseUrl'],
            'n:srcset' => [$this, 'parseUrl'],
            'link' => [$this, 'parseUrl'],
        ];
    }

    public function parseUrl(Tag $tag): PrintNode
    {
        $tag->outputMode = $tag::OutputKeepIndentation;
        $tag->expectArguments();
        $node = new PrintNode;
        $node->expression = $tag->parser->parseUnquotedStringOrExpression();
        $arguments = new ArrayNode;
        if ($tag->parser->stream->tryConsume(',')) {
            $arguments = $tag->parser->parseArguments();
        }

        $node->modifier = $tag->parser->parseModifier();
        $node->modifier->escape = false;

        $routeName = $this->getRouteName($node->expression);
        $routeArguments = $this->getRouteArguments($arguments);

        if ($tag->isNAttribute()) {
            $url = ' ' . $tag->name . '="' . $this->urlGenerator->generate($routeName, ...$routeArguments) . '"';
        } else {
            $url = $this->urlGenerator->generateAbsolute($routeName, ...$routeArguments);
        }

        $node->expression = new StringNode($url);
        return $node;
    }

    private function getRouteName(ExpressionNode $node): string
    {
        if ($node instanceof StringNode) {
            return NodeHelpers::toValue($node, constants: true);
        } elseif (
            $node instanceof MethodCallNode
            || $node instanceof ClassConstantFetchNode
            || $node instanceof ConstantFetchNode
        ) {
            $expression = $node->print(new PrintContext());
            return eval("return $expression;");
        } elseif ($node instanceof VariableNode) {
            throw new CompileException($node::class . ' not implemented');
        } else {
            throw new CompileException($node::class . ' not implemented');
        }
    }

    private function getRouteArguments(ArrayNode $arguments): mixed
    {
        try {
            return NodeHelpers::toValue($arguments, constants: true);
        } catch (\InvalidArgumentException) {
            return null;
        }
    }
}