<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Extensions\Url;

use Latte\Compiler\NodeHelpers;
use Latte\Compiler\Tag;
use Latte\Compiler\Nodes\Php;
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
        $args = new Php\Expression\ArrayNode;
        if ($tag->parser->stream->tryConsume(',')) {
            $args = $tag->parser->parseArguments();
        }

        $node->modifier = $tag->parser->parseModifier();
        $node->modifier->escape = false;

        $expr = self::toValue($node->expression);
        $values = self::toValue($args);

        if ($tag->isNAttribute()) {
            $url = ' ' . $tag->name . '="' . $this->urlGenerator->generate($expr, ...$values) . '"';
        } else {
            $url = $this->urlGenerator->generateAbsolute($expr, ...$values);
        }

        $node->expression = new Php\Scalar\StringNode($url);
        return $node;
    }

    public static function toValue($args): mixed
    {
        try {
            return NodeHelpers::toValue($args, constants: true);
        } catch (\InvalidArgumentException) {
            return null;
        }
    }
}