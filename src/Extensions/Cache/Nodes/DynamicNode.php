<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Extensions\Cache\Nodes;

use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\Position;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

/**
 * {dynamic} ... {/dynamic}
 * @todo This class is wrong
 */
final class DynamicNode extends StatementNode
{
    public ArrayNode $args;
    public AreaNode $content;
    public ?Position $endLine;

    /** @return \Generator<int, ?array, array{AreaNode, ?Tag}, static> */
    public static function create(Tag $tag): \Generator
    {
        $node = $tag->node = new static;
        $node->args = $tag->parser->parseArguments();
        [$node->content, $endTag] = yield;
        $node->endLine = $endTag?->position;
        return $node;
    }

    public function print(PrintContext $context): string
    {
        return $context->format(
            <<<'XX'
				if ($this->global->cache->createCache(%dump, %node?)) %line
				try {
					%node
					$this->global->cache->end() %line;
				} catch (\Throwable $ʟ_e) {
					$this->global->cache->rollback();
					throw $ʟ_e;
				}


				XX,
            base64_encode(random_bytes(10)),
            $this->args,
            $this->position,
            $this->content,
            $this->endLine,
        );
    }

    public function &getIterator(): \Generator
    {
        yield $this->args;
        yield $this->content;
    }
}