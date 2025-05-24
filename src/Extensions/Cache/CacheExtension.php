<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Extensions\Cache;

use Generator;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Latte\Compiler\Nodes\TemplateNode;
use Latte\Compiler\Tag;
use Latte\Engine;
use Latte\Extension;

final class CacheExtension extends Extension
{
    private bool $used;

    public function __construct(private readonly CacheInterface $cache)
    {
    }

    public function beforeCompile(Engine $engine): void
    {
        $this->used = false;
    }

    public function getTags(): array
    {
        return [
            'cache' => function (Tag $tag): Generator {
                $this->used = true;
                return yield from Nodes\CacheNode::create($tag);
            },
            'dynamic' => function (Tag $tag): Generator {
                return yield from Nodes\DynamicNode::create($tag);
            }
        ];
    }

    public function getPasses(): array
    {
        return [
            'cacheInitialization' => function (TemplateNode $node): void {
                if ($this->used) {
                    $node->head->append(new AuxiliaryNode(fn() => '$this->global->cache->initialize($this);'));
                }
            }
        ];
    }

    public function getProviders(): array
    {
        return [
            'cache' => new CacheRuntime($this->storage),
        ];
    }

    public function getCacheKey(Engine $engine): array
    {
        return [
            'version' => 2
        ]; // @todo Do we need this ???
    }
}