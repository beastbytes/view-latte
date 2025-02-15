<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Extension;

use Latte\Compiler\NodeHelpers;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\FilterNode;
use Latte\Compiler\Nodes\Php\IdentifierNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Tag;
use Latte\Engine;
use Latte\Essential\Nodes\PrintNode;
use Latte\Extension;
use Latte\Runtime\FilterInfo;
use Yiisoft\Router\UrlGeneratorInterface;

class UrlGeneratorExtension extends Extension
{
    /** @var callable|UrlGeneratorInterface|null */
    private $generator;

    public function __construct(
        callable|UrlGeneratorInterface|null $generator,
        private ?string $key = null,
    ) {
        if ($generator instanceof UrlGeneratorInterface) {
            $this->generator = [$generator, 'generate'];
        } else {
            $this->generator = $generator;
        }
    }

    public function getTags(): array
    {
        return [
            'h' => [$this, 'parseGenerate'],
            'link' => fn(Tag $tag) => yield from GenerateUrlNode::create($tag, $this->key
                ? $this->generator 
                : null
            ),
        ];
    }

    public function getFilters(): array
    {
        return [
            'link' => fn(FilterInfo $fi, ...$args) => $this->generator
                ? ($this->generator)(...$args)
                : $args[0],
        ];
    }

    public function getCacheKey(Engine $engine): mixed
    {
        return $this->key;
    }

    /**
     * {>...}
     */
    public function parseGenerate(Tag $tag): PrintNode
    {
        $tag->outputMode = $tag::OutputKeepIndentation;
        $tag->expectArguments();
        $node = new PrintNode;
        $node->expression = $tag->parser->parseUnquotedStringOrExpression();
        $args = new ArrayNode;
        if ($tag->parser->stream->tryConsume(',')) {
            $args = $tag->parser->parseArguments();
        }

        $node->modifier = $tag->parser->parseModifier();
        $node->modifier->escape = true;

        if ($this->generator
            && $this->key
            && ($expr = self::toValue($node->expression))
            && is_array($values = self::toValue($args))
            && is_string($url = ($this->generator)($expr, ...$values))
        ) {
            $node->expression = new StringNode($url);
            return $node;
        }

        array_unshift(
            $node->modifier->filters,
            new FilterNode(new IdentifierNode('link'),
            $args->toArguments())
        );
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