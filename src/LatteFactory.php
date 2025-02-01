<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte;

use Latte\Engine;
use Latte\RuntimeException;

final class LatteFactory
{
    private const EXCEPTION_MESSAGE = 'Implement the `__invoke()` method in %s provider `%s`';

    /**
     * @param string $cacheDir
     * @param FilterProvider[] $filterProviders
     * @param FunctionProvider[] $functionProviders
     */
    public function __construct(
        private readonly string $cacheDir,
        private readonly array $filterProviders = [],
        private readonly array $functionProviders = [],
    )
    {
    }

    public function create(): Engine
    {
        $latte = new Engine();
        $latte->setTempDirectory($this->cacheDir);
        $latte->setStrictTypes(true);

        foreach ($this->filterProviders as $provider) {
            if ($this->validateProvider($provider, 'filter')) {
                $latte->addFilter($provider->getName(), $provider);
            }
        }

        foreach ($this->functionProviders as $provider) {
            if ($this->validateProvider($provider, 'function')) {
                $latte->addFunction($provider->getName(), $provider);
            }
        }

        return $latte;
    }

    private function validateProvider(Provider $provider, string $type): bool
    {
        if (method_exists($provider, '__invoke')) {
            return true;
        }

        throw new RuntimeException(sprintf(self::EXCEPTION_MESSAGE, $type, $provider::class));
    }
}