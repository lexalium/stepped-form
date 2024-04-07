<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Tests;

final class Entity
{
    public function __construct(
        public string $public,
        protected int $protected,
        private float $private,
        public readonly bool $publicReadonly,
        protected readonly string $protectedReadonly,
        private readonly string $privateReadonly,
        private readonly ?Entity $nested = null,
    ) {
    }

    public function getPrivate(): float
    {
        return $this->private;
    }

    public function getPrivateReadonly(): string
    {
        return $this->privateReadonly;
    }

    public function getNested(): ?Entity
    {
        return $this->nested;
    }
}
