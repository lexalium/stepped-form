<?php

declare(strict_types=1);

namespace Lexal\SteppedForm\Data;

interface FormDataInterface
{
    /**
     * Sets a step data to the storage
     */
    public function set(string $key, mixed $data): self;

    /**
     * Gets step data
     */
    public function get(string $key): mixed;

    /**
     * Returns a last saved data
     */
    public function getLast(): mixed;

    /**
     * Returns true if the step contains data
     */
    public function has(string $key): bool;

    /**
     * Removes step data
     */
    public function forget(string $key): self;

    /**
     * Removes step data after given
     */
    public function forgetAfter(string $key): self;

    /**
     * Removes all data
     */
    public function finish(): self;
}
