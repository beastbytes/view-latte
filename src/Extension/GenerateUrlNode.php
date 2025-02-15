<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Extension;

use Generator;
use Latte\Compiler\NodeHelpers;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\NopNode;
use Latte\Compiler\Nodes\Php;
use Latte\Compiler\Nodes\Php\ModifierNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\Nodes\TextNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

/**
 * {generateUrl} ... {/generateUrl}
 */
class GenerateUrlNode extends StatementNode
{
	public AreaNode $content;
	public ModifierNode $modifier;

	/** @return Generator<int, ?array, array{AreaNode, ?Tag}, static|NopNode> */
	public static function create(Tag $tag, ?callable $generator): Generator
	{
		$tag->outputMode = $tag::OutputKeepIndentation;

		$node = $tag->node = new static;
		$args = $tag->parser->parseArguments();
		$node->modifier = $tag->parser->parseModifier();
		$node->modifier->escape = true;
		if ($tag->void) {
			return new NopNode;
		}

		[$node->content] = yield;

		if ($text = NodeHelpers::toText($node->content)) {
			if ($generator
				&& is_array($values = UrlGeneratorExtension::toValue($args))
				&& is_string($url = $generator($text, ...$values))
			) {
				$node->content = new TextNode($url);
				return $node;
			}
			$node->content = new TextNode($text);
		}

		array_unshift(
            $node->modifier->filters, 
            new Php\FilterNode(new Php\IdentifierNode('link'),
            $args->toArguments())
        );

		return $node;
	}


	public function print(PrintContext $context): string
	{
		if ($this->content instanceof TextNode) {
			return $context->format(
				<<<'XX'
					$ʟ_fi = new LR\FilterInfo(%dump);
					echo %modifyContent(%dump) %line;
					XX,
				$context->getEscaper()->export(),
				$this->modifier,
				$this->content->content,
				$this->position,
			);

		} else {
			return $context->format(
				<<<'XX'
					ob_start(fn() => ''); try {
						%node
					} finally {
						$ʟ_tmp = ob_get_clean();
					}
					$ʟ_fi = new LR\FilterInfo(%dump);
					echo %modifyContent($ʟ_tmp) %line;
					XX,
				$this->content,
				$context->getEscaper()->export(),
				$this->modifier,
				$this->position,
			);
		}
	}


	public function &getIterator(): Generator
	{
		yield $this->content;
		yield $this->modifier;
	}
}
